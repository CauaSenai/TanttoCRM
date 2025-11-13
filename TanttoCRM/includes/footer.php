<?php
// Common footer: closes body/html and includes small helper JS
?>

    </main>

<script>
// Populate data-label in tables for responsive display (used by CSS)
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.table').forEach(function(table){
    var headers = Array.from(table.querySelectorAll('thead th')).map(function(th){ return th.textContent.trim(); });
    table.querySelectorAll('tbody tr').forEach(function(row){
      Array.from(row.children).forEach(function(td, i){
        if(!td.hasAttribute('data-label')) td.setAttribute('data-label', headers[i] || '');
      });
    });
  });
});
</script>

<script>
// Handle responsive navigation
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.querySelector('.nav-toggle');
    if (navToggle) {
        navToggle.addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('show');
        });
    }
});

// Handle action dropdowns
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate table responsive data-labels
    document.querySelectorAll('.table').forEach(function(table) {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        table.querySelectorAll('tbody tr').forEach(row => {
            Array.from(row.children).forEach((td, i) => {
                if (!td.hasAttribute('data-label')) td.setAttribute('data-label', headers[i] || '');
            });
        });
    });

    // Handle action dropdown menus
    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => menu.classList.remove('show'));
    }

    document.addEventListener('click', function(e) {
        const dropdown = e.target.closest('.action-dropdown');
        
        if (dropdown && e.target.matches('.dropdown-toggle')) {
            const menu = dropdown.querySelector('.dropdown-menu');
            const isOpen = menu.classList.contains('show');
            
            closeAllDropdowns();
            
            if (!isOpen) {
                menu.classList.add('show');
            }
            
            e.preventDefault();
        } else if (!e.target.closest('.dropdown-menu')) {
            closeAllDropdowns();
        }
    });
});
</script>

</body>
</html>
