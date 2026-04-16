document.addEventListener('DOMContentLoaded', function () {
    const passwordInputs = document.querySelectorAll('input[type="password"]:not([data-no-toggle="true"])');

    passwordInputs.forEach(function (input) {
        if (input.dataset.toggleBound === 'true') {
            return;
        }

        input.dataset.toggleBound = 'true';

        const wrapper = document.createElement('div');
        wrapper.className = 'relative';

        const parent = input.parentNode;
        parent.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        if (!input.classList.contains('pr-10')) {
            input.classList.add('pr-10');
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('aria-label', 'Show password');
        button.className = 'absolute inset-y-0 right-0 inline-flex items-center px-3 text-gray-500 hover:text-gray-700';
        button.innerHTML = '<i class="fa-regular fa-eye"></i>';

        button.addEventListener('click', function () {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');

            const icon = button.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            }
        });

        wrapper.appendChild(button);
    });
});
