<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <!-- <x-app-logo /> --> Brothers Logo
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Dashboards -->
    <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
      
        <i class="menu-icon tf-icons bx bx-grid-alt"></i>
        <div>Dashboard</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('invoice*') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('invoice') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-file"></i>
       
        <div>Invoice</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('customers') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('customers') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div>Customers</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('sales*') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('sales') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-cart"></i>
        <!-- <i class="menu-icon tf-icons bx bx-store"></i> -->
        <div>Sales</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('inventory*') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('inventory') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-package"></i>
        <div>Inventory</div>
      </a>
    </li>

    <!-- Settings -->
    <li class="menu-item {{ request()->is('settings/*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div class="text-truncate">{{ __('Settings') }}</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.profile') }}" wire:navigate>{{ __('Profile') }}</a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.password') }}" wire:navigate>{{ __('Password') }}</a>
        </li>
      </ul>
    </li>
  </ul>
</aside>
<!-- / Menu -->

<script>
  // Toggle the 'open' class when the menu-toggle is clicked
  document.querySelectorAll('.menu-toggle').forEach(function(menuToggle) {
    menuToggle.addEventListener('click', function() {
      const menuItem = menuToggle.closest('.menu-item');
      // Toggle the 'open' class on the clicked menu-item
      menuItem.classList.toggle('open');
    });
  });
</script>
