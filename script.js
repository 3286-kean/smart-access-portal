const translations = {
    en: {
        languageLabel: 'Language',
        homeTitle: 'University Smart Portal',
        studentBtn: 'Student',
        adminBtn: 'Admin',
        loginTitle: 'Login',
        studentLoginTitle: 'Student Login',
        adminLoginTitle: 'Admin Login',
        emailPlaceholder: 'Email',
        passwordPlaceholder: 'Password',
        showPassword: 'Show',
        hidePassword: 'Hide',
        rememberMe: 'Remember me',
        signInButton: 'Sign In',
        backButton: 'Back'
    },
    sw: {
        languageLabel: 'Lugha',
        homeTitle: 'Tovuti Mahiri ya Chuo Kikuu',
        studentBtn: 'Mwanafunzi',
        adminBtn: 'Msimamizi',
        loginTitle: 'Ingia',
        studentLoginTitle: 'Ingia kama Mwanafunzi',
        adminLoginTitle: 'Ingia kama Msimamizi',
        emailPlaceholder: 'Barua pepe',
        passwordPlaceholder: 'Nenosiri',
        showPassword: 'Onyesha',
        hidePassword: 'Ficha',
        rememberMe: 'Nikumbuke',
        signInButton: 'Ingia',
        backButton: 'Rudi'
    }
};

const languageSelect = document.querySelector('#language-select');
const roleScreen = document.querySelector('#role-screen');
const authScreen = document.querySelector('#auth-screen');
const loginTitle = document.querySelector('#login-title');
const roleInput = document.querySelector('#role-input');
const passwordInput = document.querySelector('#password');
const passwordToggle = document.querySelector('#togglePassword');
const authPanel = authScreen?.firstElementChild;

function applyLanguage(language) {
    const words = translations[language] ?? translations.en;
    document.documentElement.lang = language in translations ? language : 'en';

    document.querySelectorAll('[data-i18n]').forEach((element) => {
        const translated = words[element.dataset.i18n];
        if (!translated) return;

        if (element instanceof HTMLInputElement) {
            element.placeholder = translated;
        } else {
            element.textContent = translated;
        }
    });
}

function showLogin(role) {
    roleInput.value = role;
    loginTitle.dataset.i18n = `${role}LoginTitle`;
    roleScreen.classList.add('hidden');
    authScreen.classList.remove('hidden');
    applyLanguage(languageSelect.value);
    document.querySelector('#login-form input[type="email"]')?.focus();
}

function showRoles() {
    authScreen.classList.add('hidden');
    roleScreen.classList.remove('hidden');
    roleInput.value = '';
    passwordInput.type = 'password';
    passwordInput.value = '';
    passwordToggle.dataset.i18n = 'showPassword';
    passwordToggle.setAttribute('aria-pressed', 'false');
    loginTitle.dataset.i18n = 'loginTitle';
    applyLanguage(languageSelect.value);
}

if (languageSelect && roleScreen && authScreen && loginTitle && roleInput) {
    languageSelect.addEventListener('change', () => applyLanguage(languageSelect.value));
    document.querySelector('#choose-student')?.addEventListener('click', () => showLogin('student'));
    document.querySelector('#choose-admin')?.addEventListener('click', () => showLogin('admin'));

    if (authPanel) {
        const backButton = document.createElement('button');
        backButton.type = 'button';
        backButton.className = 'auth-back';
        backButton.dataset.i18n = 'backButton';
        backButton.addEventListener('click', showRoles);
        authPanel.insertBefore(backButton, loginTitle);
    }

    languageSelect.value = languageSelect.value in translations ? languageSelect.value : 'en';
    applyLanguage(languageSelect.value);

    if (authScreen.querySelector('.bg-red-100')) {
        roleScreen.classList.add('hidden');
        authScreen.classList.remove('hidden');
    }
}

passwordToggle?.addEventListener('click', () => {
    const isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';
    passwordToggle.dataset.i18n = isHidden ? 'hidePassword' : 'showPassword';
    passwordToggle.setAttribute('aria-pressed', String(isHidden));
    applyLanguage(languageSelect.value);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && authScreen && !authScreen.classList.contains('hidden')) {
        showRoles();
    }
});