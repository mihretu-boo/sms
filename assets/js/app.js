/**
 * SJASSMS — School Management System
 * Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

  // ===== Sidebar Toggle =====
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  function isMobile() { return window.innerWidth < 992; }

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function () {
      if (isMobile()) {
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('show');
      } else {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
      }
    });
  }

  if (mobileSidebarToggle) {
    mobileSidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('mobile-open');
      sidebarOverlay.classList.toggle('show');
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function () {
      sidebar.classList.remove('mobile-open');
      sidebarOverlay.classList.remove('show');
    });
  }

  // Restore sidebar state on desktop
  if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
    sidebar && sidebar.classList.add('collapsed');
  }


  // ===== DataTables Auto-Init =====
  var tables = document.querySelectorAll('table[id$="Table"]:not([data-no-dt])');
  tables.forEach(function (t) {
    if (!$.fn.DataTable.isDataTable('#' + t.id)) {
      // Count actual header columns
      var headerCols = t.querySelectorAll('thead tr:first-child th, thead tr:first-child td').length;
      // Ensure every tbody row has the exact same cell count (fix colspan empty rows)
      t.querySelectorAll('tbody tr').forEach(function (row) {
        var cells = row.querySelectorAll('td, th');
        if (cells.length === 1 && cells[0].colSpan > 1) {
          // Replace colspan cell with proper cells
          var msg = cells[0].innerHTML;
          cells[0].removeAttribute('colspan');
          cells[0].style.display = '';
          for (var i = 1; i < headerCols; i++) {
            var td = document.createElement('td');
            row.appendChild(td);
          }
        }
      });

      try {
        $('#' + t.id).DataTable({
          responsive: false,
          pageLength: 25,
          language: {
            search: '',
            searchPlaceholder: 'Quick search...',
            lengthMenu: 'Show _MENU_ entries',
            emptyTable: 'No data available',
          },
          columnDefs: [{ targets: '_all', defaultContent: '' }],
          dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        });
      } catch (e) {
        console.warn('DataTables init failed for #' + t.id, e);
      }
    }
  });


  // ===== Flatpickr Date Pickers =====
  var datepickers = document.querySelectorAll('.flatpickr');
  datepickers.forEach(function (el) {
    flatpickr(el, {
      dateFormat: 'Y-m-d',
      allowInput: true,
    });
  });

  var datetimepickers = document.querySelectorAll('.flatpickr-datetime');
  datetimepickers.forEach(function (el) {
    flatpickr(el, {
      dateFormat: 'Y-m-d H:i',
      enableTime: true,
      time_24hr: true,
      allowInput: true,
    });
  });


  // ===== Select2 Auto-Init =====
  if (typeof $.fn.select2 !== 'undefined') {
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    $('.select2-tags').select2({ theme: 'bootstrap-5', tags: true, width: '100%' });
  }


  // ===== SweetAlert2 Confirmations =====
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      var msg    = el.dataset.confirm || 'Are you sure?';
      var form   = el.closest('form');
      var href   = el.href;
      var method = el.dataset.method || (form ? form.method : 'GET');

      Swal.fire({
        title: 'Confirm Action',
        text: msg,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1565C0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
      }).then(function (result) {
        if (result.isConfirmed) {
          if (form) form.submit();
          else if (href) window.location.href = href;
        }
      });
    });
  });


  // ===== Auto-dismiss alerts after 5s =====
  document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function (alert) {
    setTimeout(function () {
      var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) bsAlert.close();
    }, 6000);
  });


  // ===== Notification mark-read =====
  document.querySelectorAll('.notif-item').forEach(function (item) {
    item.addEventListener('click', function () {
      var id = item.dataset.id;
      if (id) {
        fetch(BASE_URL + '/api/notifications/read', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': CSRF_TOKEN,
          },
          body: 'id=' + id + '&_csrf_token=' + encodeURIComponent(CSRF_TOKEN),
        }).catch(function () {});
      }
    });
  });


  // ===== Confirm delete forms =====
  document.querySelectorAll('form[data-delete]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = form.dataset.name || 'this item';
      Swal.fire({
        title: 'Delete ' + name + '?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
      }).then(function (result) {
        if (result.isConfirmed) form.submit();
      });
    });
  });


  // ===== Global AJAX setup for CSRF =====
  $(document).ajaxSend(function (event, jqXHR) {
    jqXHR.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
  });


  // ===== Dynamic subject loading =====
  var classSelector = document.getElementById('classSelect');
  if (classSelector) {
    classSelector.addEventListener('change', function () {
      var classId  = this.value;
      var subSelect = document.getElementById('subjectSelect');
      if (!subSelect) return;
      subSelect.innerHTML = '<option value="">Loading...</option>';
      fetch(BASE_URL + '/api/subjects?class_id=' + classId + '&semester_id=' + (window.SEMESTER_ID || 1))
        .then(r => r.json())
        .then(function (subjects) {
          subSelect.innerHTML = '<option value="">Select Subject</option>';
          subjects.forEach(function (s) {
            subSelect.insertAdjacentHTML('beforeend', '<option value="' + s.id + '">' + s.name + ' (' + s.code + ')</option>');
          });
        });
    });
  }


  // ===== Print handler =====
  document.querySelectorAll('[data-print]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = btn.dataset.print;
      if (url) window.open(url, '_blank', 'width=800,height=600');
    });
  });


  // ===== Tooltip init =====
  var tooltips = document.querySelectorAll('[title]');
  tooltips.forEach(function (el) {
    new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'top' });
  });


  // ===== Progress bar for form submit =====
  document.querySelectorAll('form:not([data-no-loading])').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
      }
    });
  });


  // ===== Sidebar active link highlight based on URL =====
  var path = window.location.pathname.replace(/^\/[^/]+\//, '/');
  document.querySelectorAll('.sidebar-link, .sidebar-sublink').forEach(function (link) {
    var href = link.getAttribute('href');
    if (href && href !== '#' && path.startsWith(href.replace(/^\/[^/]+/, ''))) {
      link.classList.add('active');
      // Expand parent collapse
      var collapse = link.closest('.collapse');
      if (collapse) {
        var bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false });
        bsCollapse.show();
        var trigger = document.querySelector('[href="#' + collapse.id + '"]');
        if (trigger) {
          trigger.classList.add('active');
          trigger.setAttribute('aria-expanded', 'true');
        }
      }
    }
  });


  // ===== Back to top button =====
  var backToTop = document.createElement('button');
  backToTop.innerHTML = '<i class="fas fa-chevron-up"></i>';
  backToTop.className = 'btn btn-primary btn-sm position-fixed rounded-circle shadow d-none';
  backToTop.style.cssText = 'bottom:20px;right:20px;width:36px;height:36px;z-index:9999;display:flex;align-items:center;justify-content:center;';
  document.body.appendChild(backToTop);

  window.addEventListener('scroll', function () {
    if (window.scrollY > 300) backToTop.classList.remove('d-none');
    else backToTop.classList.add('d-none');
  });

  backToTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });


  // ===== Real-time clock in topbar =====
  function updateClock() {
    var el = document.getElementById('realTimeClock');
    if (el) {
      var now = new Date();
      el.textContent = now.toLocaleTimeString('en-ET', { hour: '2-digit', minute: '2-digit' });
    }
  }
  updateClock();
  setInterval(updateClock, 30000);

}); // end DOMContentLoaded
