import './bootstrap';

import '../sass/app.scss';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function() {

    const swiper = new Swiper('.swiper', {
        loop: true,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
    });

    const checkbox = document.getElementById('has_company');
    const companyFields = document.getElementById('company_fields');
    const companyInputs = companyFields.querySelectorAll('input');

    if (checkbox.checked) {
        companyFields.style.display = 'block';
    } else {
        companyFields.style.display = 'none';
    }

    function resetCompanyFields() {
        companyInputs.forEach(function(input) {
            input.value = '';
        });
    }

    checkbox.addEventListener('change', function() {
        if (checkbox.checked) {
            companyFields.style.display = 'block';
        } else {
            companyFields.style.display = 'none';
            resetCompanyFields();
        }
    });
});
