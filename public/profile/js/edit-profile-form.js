// Store Quill instances outside Alpine's reactive proxy to avoid conflicts
const editorInstances = new Map();
const IMAGE_LOAD_TIMEOUT_MS = 8000;

document.addEventListener('alpine:init', () => {
    Alpine.data('editProfileForm', (config = {}) => ({
        name: config.initial?.name || '',
        email: config.initial?.email || '',
        phone: config.initial?.phone || '',
        introduction_line: config.initial?.introduction_line || '',
        suburb: config.initial?.suburb || '',
        profile_text: config.initial?.profile_text || '',

        age_group: config.initial?.age_group || '',
        hair_color: config.initial?.hair_color || '',
        hair_length: config.initial?.hair_length || '',
        ethnicity: config.initial?.ethnicity || '',
        body_type: config.initial?.body_type || '',
        bust_size: config.initial?.bust_size || '',
        your_length: config.initial?.your_length || '',

        primaryIdentity: Array.isArray(config.initial?.primaryIdentity) ? config.initial.primaryIdentity : [],
        attributes: Array.isArray(config.initial?.attributes) ? config.initial.attributes : [],
        servicesStyle: Array.isArray(config.initial?.servicesStyle) ? config.initial.servicesStyle : [],
        services_provided: Array.isArray(config.initial?.services_provided) ? config.initial.services_provided : [],

        availability: config.initial?.availability || '',
        contact_method: config.initial?.contact_method || '',
        phone_contact: config.initial?.phone_contact || '',
        time_waster: config.initial?.time_waster || '',

        twitter_handle: config.initial?.twitter_handle || '',
        website: config.initial?.website || '',
        onlyfans_username: config.initial?.onlyfans_username || '',

        searchResults: [],
        showResults: false,
        searching: false,
        debounceTimer: null,
        suburbSelected: Boolean(config.initial?.suburbSelected),

        submitting: false,
        fieldErrors: {},

        submitUrl: config.submitUrl || '',
        csrfToken: config.csrfToken || '',

        init() {
            this.fieldErrors = this.normalizeErrors(config.initial?.serverErrors);
            this.initEditors();
        },

        normalizeErrors(rawErrors) {
            if (!rawErrors || typeof rawErrors !== 'object') {
                return {};
            }

            if (Array.isArray(rawErrors)) {
                return rawErrors.length > 0 ? { _form: rawErrors } : {};
            }

            return Object.entries(rawErrors).reduce((carry, [field, messages]) => {
                if (Array.isArray(messages) && messages.length > 0) {
                    carry[field] = messages;
                } else if (typeof messages === 'string' && messages.trim() !== '') {
                    carry[field] = [messages];
                }

                return carry;
            }, {});
        },

        hasFieldError(field) {
            return Array.isArray(this.fieldErrors[field]) && this.fieldErrors[field].length > 0;
        },

        getFieldError(field) {
            return this.hasFieldError(field) ? this.fieldErrors[field][0] : '';
        },

        getEditor(key) {
            return editorInstances.get(key);
        },

        initEditors() {
            this.$nextTick(() => {
                this.createEditor(
                    'introduction_line_editor',
                    'introduction_line',
                    'Write your introduction line here...',
                    { imageUpload: true }
                );

                this.createEditor(
                    'profile_text_editor',
                    'profile_text',
                    'Write your profile description here...',
                    { imageUpload: true }
                );
            });
        },

        createEditor(elementId, modelKey, placeholder, options = {}) {
            const element = document.querySelector(`#${elementId}`);

            if (!element || editorInstances.has(elementId)) {
                return;
            }

            const toolbarOptions = options.imageUpload
                ? [
                      [{ header: [1, 2, 3, false] }],
                      ['bold', 'italic', 'underline'],
                      [{ list: 'ordered' }, { list: 'bullet' }],
                      ['link', 'blockquote', 'image'],
                      ['clean'],
                  ]
                : [
                      [{ header: [1, 2, 3, false] }],
                      ['bold', 'italic', 'underline'],
                      [{ list: 'ordered' }, { list: 'bullet' }],
                      ['link', 'blockquote'],
                      ['clean'],
                  ];

            const quill = new Quill(element, {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions,
                },
                placeholder,
            });

            editorInstances.set(elementId, quill);

            if (this[modelKey]) {
                quill.clipboard.dangerouslyPasteHTML(0, this[modelKey]);
            }

            if (options.imageUpload) {
                quill.getModule('toolbar').addHandler('image', async () => {
                    const result = await Swal.fire({
                        title: 'Insert image',
                        input: 'url',
                        inputLabel: 'Paste image URL',
                        inputPlaceholder: 'https://your-image-url.com/photo.jpg',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: 'Insert URL',
                        denyButtonText: 'Upload from device',
                        cancelButtonText: 'Cancel',
                        inputValidator: async (value) => {
                            const trimmedValue = (value || '').trim();

                            if (trimmedValue === '') {
                                return 'Please enter an image URL or choose upload.';
                            }

                            if (!this.isValidImageUrl(trimmedValue)) {
                                return 'Invalid image URL. Please use a valid http/https URL.';
                            }

                            const canLoadImage = await this.canLoadImageUrl(trimmedValue);
                            if (!canLoadImage) {
                                return 'Unable to load a valid image from this URL.';
                            }

                            return null;
                        },
                    });

                    if (result.isConfirmed) {
                        const imageUrl = (result.value || '').trim();
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', imageUrl);
                        quill.setSelection(range.index + 1);
                        return;
                    }

                    if (!result.isDenied) {
                        return;
                    }

                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/jpeg,image/jpg,image/png,image/webp');
                    input.click();

                    input.addEventListener('change', async () => {
                        const file = input.files[0];
                        if (!file) return;

                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                        if (!allowedTypes.includes(file.type)) {
                            this.error('Invalid file type. Please upload a jpg, jpeg, png, or webp image.');
                            return;
                        }

                        const maxSizeBytes = 5 * 1024 * 1024;
                        if (file.size > maxSizeBytes) {
                            this.error('Image is too large. Maximum allowed size is 5 MB.');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('_token', this.csrfToken);

                        try {
                            const response = await fetch('/editor/upload-image', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            if (!response.ok) {
                                const data = await response.json().catch(() => ({}));
                                throw new Error(data.message || 'Upload failed');
                            }

                            const data = await response.json();
                            const range = quill.getSelection(true);
                            quill.insertEmbed(range.index, 'image', data.url);
                            quill.setSelection(range.index + 1);
                        } catch (err) {
                            console.error('Image upload error:', err);
                            this.error(err.message || 'Failed to upload image. Please try again.');
                        }
                    });
                });
            }

            quill.on('text-change', () => {
                this[modelKey] = quill.root.innerHTML;

                if (modelKey === 'introduction_line' && this.$refs.introductionLineInput) {
                    this.$refs.introductionLineInput.value = this[modelKey];
                }

                if (modelKey === 'profile_text' && this.$refs.profileTextInput) {
                    this.$refs.profileTextInput.value = this[modelKey];
                }
            });
        },

        isValidImageUrl(url) {
            try {
                const parsed = new URL(url);
                return parsed.protocol === 'http:' || parsed.protocol === 'https:';
            } catch {
                return false;
            }
        },

        canLoadImageUrl(url) {
            return new Promise((resolve) => {
                const image = new Image();
                let timeoutId = null;
                let settled = false;
                const finalize = (result) => {
                    if (settled) {
                        return;
                    }
                    settled = true;
                    if (timeoutId !== null) {
                        window.clearTimeout(timeoutId);
                    }
                    image.onload = null;
                    image.onerror = null;
                    image.removeAttribute('src');
                    resolve(result);
                };

                timeoutId = window.setTimeout(() => finalize(false), IMAGE_LOAD_TIMEOUT_MS);
                image.onload = () => {
                    finalize(true);
                };
                image.onerror = () => {
                    finalize(false);
                };

                image.src = url;
            });
        },

        syncHiddenEditorInputs() {
            const introductionEditor = this.getEditor('introduction_line_editor');
            const profileEditor = this.getEditor('profile_text_editor');

            if (introductionEditor) {
                this.introduction_line = introductionEditor.root.innerHTML;
            }

            if (profileEditor) {
                this.profile_text = profileEditor.root.innerHTML;
            }

            if (this.$refs.introductionLineInput) {
                this.$refs.introductionLineInput.value = this.introduction_line || '';
            }

            if (this.$refs.profileTextInput) {
                this.$refs.profileTextInput.value = this.profile_text || '';
            }
        },

        toggleTag(group, tag, event) {
            if (group === 'primaryIdentity') {
                this.primaryIdentity = [tag];
                return;
            }

            if (group === 'attributes') {
                if (this.attributes.includes(tag)) {
                    this.attributes = this.attributes.filter((item) => item !== tag);
                } else {
                    this.attributes.push(tag);
                }
                return;
            }

            if (group === 'servicesStyle') {
                if (this.servicesStyle.includes(tag)) {
                    this.servicesStyle = this.servicesStyle.filter((item) => item !== tag);
                } else if (this.servicesStyle.length < 12) {
                    this.servicesStyle.push(tag);
                } else if (event?.currentTarget) {
                    const element = event.currentTarget;
                    element.classList.add('shake');
                    setTimeout(() => element.classList.remove('shake'), 300);
                }
            }
        },

        toggleService(service) {
            if (this.services_provided.includes(service)) {
                this.services_provided = this.services_provided.filter((item) => item !== service);
            } else {
                this.services_provided.push(service);
            }
        },

        handleSuburbInput() {
            this.suburbSelected = false;
            this.searchSuburbs();
        },

        handleSuburbBlur() {
            setTimeout(() => {
                this.showResults = false;
            }, 200);
        },

        searchSuburbs() {
            if (!this.suburb || this.suburb.trim().length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }

            clearTimeout(this.debounceTimer);

            this.debounceTimer = setTimeout(() => {
                this.searching = true;

                fetch(`/api/suburbs/search?q=${encodeURIComponent(this.suburb.trim())}`)
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Failed to fetch suburbs');
                        }
                        return response.json();
                    })
                    .then((data) => {
                        this.searchResults = Array.isArray(data) ? data : [];
                        this.showResults = this.searchResults.length > 0;
                    })
                    .catch((error) => {
                        console.error('Suburb search error:', error);
                        this.searchResults = [];
                        this.showResults = false;
                    })
                    .finally(() => {
                        this.searching = false;
                    });
            }, 300);
        },

        selectSuburb(item) {
            this.suburb = `${item.suburb}, ${item.state} ${item.postcode}`;
            this.suburbSelected = true;
            this.showResults = false;
            this.searchResults = [];
        },

        stripHtml(html) {
            const temp = document.createElement('div');
            temp.innerHTML = html || '';
            return (temp.textContent || temp.innerText || '').replace(/\u00A0/g, ' ').trim();
        },

        validate() {
            const errors = {};
            const setFieldError = (field, message) => {
                if (!errors[field]) {
                    errors[field] = [message];
                }
            };

            if (!this.name.trim()) setFieldError('name', 'Profile name is required.');

            if (!this.suburb.trim()) {
                setFieldError('suburb', 'Suburb is required.');
            } else if (!this.suburbSelected) {
                setFieldError('suburb', 'Please choose a location from the dropdown list, which appears while typing.');
            }

            const plainIntroductionLine = this.stripHtml(this.introduction_line);
            if (!plainIntroductionLine) setFieldError('introduction_line', 'Introduction line is required.');

            const plainProfileText = this.stripHtml(this.profile_text);
            if (!plainProfileText) setFieldError('profile_text', 'Profile text is required.');

            if (!this.age_group) setFieldError('age_group', 'Age group is required.');
            if (!this.hair_color) setFieldError('hair_color', 'Hair color is required.');
            if (!this.hair_length) setFieldError('hair_length', 'Hair length is required.');
            if (!this.ethnicity) setFieldError('ethnicity', 'Ethnicity is required.');
            if (!this.body_type) setFieldError('body_type', 'Body type is required.');
            if (!this.bust_size) setFieldError('bust_size', 'Bust size is required.');
            if (!this.your_length) setFieldError('your_length', 'Your length is required.');
            if (this.primaryIdentity.length === 0) setFieldError('primary_identity', 'Primary identity is required.');
            if (this.attributes.length === 0) setFieldError('attributes', 'Attributes are required.');
            if (this.servicesStyle.length === 0) setFieldError('services_style', 'Services & style are required.');
            if (this.services_provided.length === 0) setFieldError('services_provided', 'Services provided are required.');
            if (!this.availability) setFieldError('availability', 'Availability is required.');
            if (!this.contact_method) setFieldError('contact_method', 'Contact method is required.');
            if (!this.phone_contact) setFieldError('phone_contact', 'Phone contact preference is required.');
            if (!this.time_waster) setFieldError('time_waster', 'Time waster shield preference is required.');

            return errors;
        },

        toast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                timer: 1800,
                showConfirmButton: false
            });
        },

        error(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        },

        scrollToErrors() {
            this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
        },

        async submitForm() {
            this.syncHiddenEditorInputs();

            this.fieldErrors = this.validate();

            if (Object.keys(this.fieldErrors).length > 0) {
                this.scrollToErrors();
                return;
            }

            this.submitting = true;

            const formData = new FormData();
            formData.append('name', this.name);
            formData.append('phone', this.phone);
            formData.append('introduction_line', this.introduction_line);
            formData.append('suburb', this.suburb);
            formData.append('profile_text', this.profile_text);
            formData.append('age_group', this.age_group);
            formData.append('hair_color', this.hair_color);
            formData.append('hair_length', this.hair_length);
            formData.append('ethnicity', this.ethnicity);
            formData.append('body_type', this.body_type);
            formData.append('bust_size', this.bust_size);
            formData.append('your_length', this.your_length);

            this.primaryIdentity.forEach((tag) => formData.append('primary_identity[]', tag));
            this.attributes.forEach((tag) => formData.append('attributes[]', tag));
            this.servicesStyle.forEach((tag) => formData.append('services_style[]', tag));
            this.services_provided.forEach((service) => formData.append('services_provided[]', service));

            formData.append('availability', this.availability);
            formData.append('contact_method', this.contact_method);
            formData.append('phone_contact', this.phone_contact);
            formData.append('time_waster', this.time_waster);

            formData.append('twitter_handle', this.twitter_handle);
            formData.append('website', this.website);
            formData.append('onlyfans_username', this.onlyfans_username);

            formData.append('_token', this.csrfToken);

            try {
                const response = await fetch(this.submitUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                let data = {};
                const contentType = response.headers.get('content-type') || '';

                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    data = { message: text };
                }

                if (response.ok) {
                    this.fieldErrors = {};
                    this.toast(data.message || 'Profile updated successfully.');
                } else if (response.status === 422) {
                    this.fieldErrors = this.normalizeErrors(data.errors || {});
                    this.scrollToErrors();
                } else {
                    this.fieldErrors = {};
                    this.error(data.message || 'Unable to save profile. Please try again later.');
                }
            } catch (error) {
                console.error('Profile submit error:', error);
                this.fieldErrors = {};
                this.error('Unable to save profile. Please check your connection and try again.');
            } finally {
                this.submitting = false;
            }
        }
    }));
});
