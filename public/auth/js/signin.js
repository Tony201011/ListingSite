function signinForm() {
    return {
        fieldOrder: ['email', 'password', 'captcha', 'remember'],
        prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        highlightedField: null,
        highlightedFieldTimeout: null,

        init() {
            this.$nextTick(() => {
                this.scrollToFirstServerError();
            });
        },

        getFieldRef(field) {
            const refMap = {
                email: 'email',
                password: 'password',
                captcha: 'captcha',
                remember: 'remember'
            };

            return this.$refs[refMap[field]] || null;
        },

        getFieldErrorContainer(field) {
            return this.$el.querySelector(`[data-error-container="${field}"]`);
        },

        getStickyOffset() {
            const stickyHeader = document.querySelector(
                'header.sticky, header[class*="sticky"], header.fixed, header[class*="fixed"]'
            );
            const stickyHeaderHeight = stickyHeader ? stickyHeader.getBoundingClientRect().height : 0;

            return stickyHeaderHeight + 16;
        },

        scrollElementIntoView(element) {
            if (!element) {
                return;
            }

            const stickyOffset = this.getStickyOffset();
            const rect = element.getBoundingClientRect();
            const bottomSpacing = 16;

            if (rect.top >= stickyOffset && rect.bottom <= window.innerHeight - bottomSpacing) {
                return;
            }

            const rawTop = window.scrollY + rect.top - stickyOffset;
            const maxScrollTop = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);
            const top = Math.min(Math.max(rawTop, 0), maxScrollTop);

            window.scrollTo({
                top,
                behavior: this.prefersReducedMotion ? 'auto' : 'smooth'
            });
        },

        getFieldScrollTarget(field) {
            const input = this.getFieldRef(field);
            const errorContainer = this.getFieldErrorContainer(field);
            const fieldGroup = errorContainer?.closest('[data-field-group]') || errorContainer?.parentElement;

            const labelByFor = input?.id ? this.$el.querySelector(`label[for="${input.id}"]`) : null;
            const labelInGroup = fieldGroup?.querySelector('label');

            return labelByFor || labelInGroup || fieldGroup || input || errorContainer;
        },

        highlightField(field) {
            const input = this.getFieldRef(field);

            if (!input) {
                return;
            }

            if (this.highlightedField && this.highlightedField !== input) {
                this.highlightedField.classList.remove('signin-invalid-focus');
            }

            clearTimeout(this.highlightedFieldTimeout);

            input.classList.add('signin-invalid-focus');
            this.highlightedField = input;
            this.highlightedFieldTimeout = setTimeout(() => {
                input.classList.remove('signin-invalid-focus');
                if (this.highlightedField === input) {
                    this.highlightedField = null;
                }
            }, 2200);
        },

        focusField(field) {
            const input = this.getFieldRef(field);

            if (!input || typeof input.focus !== 'function') {
                return;
            }

            setTimeout(() => {
                input.focus({ preventScroll: true });
                if (typeof input.reportValidity === 'function') {
                    input.reportValidity();
                }
            }, this.prefersReducedMotion ? 0 : 250);
        },

        scrollAndFocusField(field) {
            const scrollTarget = this.getFieldScrollTarget(field);

            if (!scrollTarget) {
                return;
            }

            this.scrollElementIntoView(scrollTarget);
            this.highlightField(field);
            this.focusField(field);
        },

        scrollToFirstServerError() {
            const firstServerError = this.$el.querySelector('[data-server-error="true"]');
            if (!firstServerError) {
                return;
            }

            const field = firstServerError.dataset.field;
            if (field) {
                this.scrollAndFocusField(field);
            } else {
                this.scrollElementIntoView(firstServerError);
            }
        },

        validateField(field) {
            const input = this.getFieldRef(field);

            if (!input || typeof input.checkValidity !== 'function') {
                return true;
            }

            const isValid = input.checkValidity();
            input.setAttribute('aria-invalid', isValid ? 'false' : 'true');

            return isValid;
        },

        submitForm(event) {
            let firstInvalidField = null;

            this.fieldOrder.forEach((field) => {
                const isValid = this.validateField(field);
                if (!isValid && !firstInvalidField) {
                    firstInvalidField = field;
                }
            });

            if (!firstInvalidField) {
                return;
            }

            event.preventDefault();

            this.$nextTick(() => {
                this.scrollAndFocusField(firstInvalidField);
            });
        }
    };
}
