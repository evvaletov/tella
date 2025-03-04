document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('#overlay li > a');
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            const subMenu = this.nextElementSibling;
            if (subMenu && subMenu.tagName === 'UL') {
                e.preventDefault(); // Prevent navigation on first click
                subMenu.classList.toggle('hidden');
            }
        });
    });
});