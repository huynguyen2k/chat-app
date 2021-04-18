const registerForm = document.getElementById('register-form');

registerForm.addEventListener('submit', function(event){
    event.preventDefault();

    const errorsList = Array.from(document.getElementsByClassName('form-control__message'));
    errorsList.forEach(item => item.remove());

    let password = document.getElementById('userPassword').value;
    let confirmPassword = document.getElementById('userConfirmPassword').value;

    if (password == confirmPassword) {
        this.submit();
    } else {
        const errorElement = document.createElement('p');
        const registerFormTitle = document.getElementById('register-form-title');

        errorElement.classList.add('form-control__message', 'form-control__message--error');
        errorElement.innerHTML = 'Mật khẩu và mật khẩu xác nhận không khớp';
        registerFormTitle.insertAdjacentElement('beforebegin', errorElement);
    }
});