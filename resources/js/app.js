import './bootstrap';

import Swal from 'sweetalert2';
window.Swal = Swal;

import '../sass/app.scss';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function() {

    document.getElementById('filterForm').addEventListener('submit', function (e) {
        const form = e.target;
        const elements = form.querySelectorAll('input, select');

        elements.forEach(el => {
            const isCheckbox = el.type === 'checkbox';
            const isEmpty = el.value.trim() === '';
            const name = el.name;

            if (isCheckbox) {
                if (el.checked) {
                    const hiddenInput = form.querySelector(`input[type="hidden"][name="${name}"]`);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                } else {
                    el.disabled = true;
                }
            } else if (isEmpty) {
                el.disabled = true;
            }
        });
    });

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
