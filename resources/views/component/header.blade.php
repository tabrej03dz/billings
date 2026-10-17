{{-- navbar --}}

<nav class=" text-black shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
      <!-- Logo -->
      <div class="text-2xl font-bold tracking-wide">
        Billing <span class="text-amber-800">GST</span>
      </div>

      <!-- Hamburger Icon (Mobile) -->
      <button id="menu-btn" class="md:hidden text-3xl focus:outline-none">
        ☰
      </button>


      <!-- Navigation Links -->
      <ul id="menu" class="hidden md:flex space-x-6 font-medium">
        <li><a class="hover:text-amber-800" href="#">Home</a></li>
        <li><a class="hover:text-amber-800" href="#">About</a></li>
        <li><a class="hover:text-amber-800" href="#">Features</a></li>
        <li><a class="hover:text-amber-800" href="#">GST Invoice</a></li>
        <li><a class="hover:text-amber-800" href="#">Inventory</a></li>
        <li><a class="hover:text-amber-800" href="#">Reports</a></li>
        <li><a class="hover:text-amber-800" href="#">Pricing</a></li>
        <li><a class="hover:text-amber-800" href="#">Download</a></li>
        <li><a class="hover:text-amber-800" href="#">Support</a></li>
        <li><a class="hover:text-amber-800" href="#">Contact</a></li>

        <li>
          <a class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600" href="#">Login</a>
        </li>
        <li>
          <a class="bg-amber-800 text-white px-4 py-2 rounded-md hover:bg-amber-900" href="#">Sign Up</a>
        </li>
      </ul>
    </div>

    <!-- Mobile Dropdown Menu -->
    <ul id="mobile-menu" class="md:hidden hidden flex-col  text-black space-y-4 p-4">
      <li><a href="#" class="hover:text-gray-500">Home</a></li>
      <li><a href="#" class="hover:text-gray-500">About</a></li>
      <li><a href="#" class="hover:text-gray-500">Features</a></li>
      <li><a href="#" class="hover:text-gray-500">GST Invoice</a></li>
      <li><a href="#" class="hover:text-gray-500">Inventory</a></li>
      <li><a href="#" class="hover:text-gray-500">Reports</a></li>
      <li><a href="#" class="hover:text-gray-500">Pricing</a></li>
      <li><a href="#" class="hover:text-gray-500">Download</a></li>
      <li><a href="#" class="hover:text-gray-500">Support</a></li>
      <li><a href="#" class="hover:text-gray-500">Contact</a></li>

      <li>
        <a class="bg-gray-400 text-black px-4 py-2 rounded-md" href="#">Login</a>
      </li>

      <li>
        <a class="bg-amber-700 text-black px-4 py-2 rounded-md" href="#">Sign Up</a>
      </li>
    </ul>
  </nav>

  <script>
    // Toggle Mobile Menu
    document.getElementById("menu-btn").addEventListener("click", () => {
      document.getElementById("mobile-menu").classList.toggle("hidden");
    });
  </script>

