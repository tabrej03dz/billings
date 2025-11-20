@extends('component.main')
@section('content')

{{-- Banner Section --}}
<section class="banner grid grid-cols-1 lg:grid-cols-2 gap-12 p-8 lg:px-16 bg-gray-50">
    <!-- Text Container -->
    <div class="banner-text flex flex-col justify-center ">
      <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight">
        Best Billing Software – Smart, Fast & Powerful Invoicing Solution
      </h1>
      <p class="text-gray-700 text-lg lg:text-xl leading-relaxed py-6">
        Managing invoices, payments, and customer accounts is easier than ever with the Best Billing Software — a complete, all-in-one solution designed for businesses of every size. Whether you run a small shop, a startup, or a full-scale enterprise, this billing system gives you everything you need to handle your financial operations professionally and effortlessly.
      </p>
      <a href="#"
         class="inline-block w-max px-6 py-3 bg-black text-white font-semibold rounded-xl shadow-md hover:shadow-xl hover:bg-gray-700 transition-all duration-300">
        Demo Now
      </a>
    </div>

    <!-- Image Container -->
    <div class="banner-image flex justify-center items-center">
      <div class="relative transform transition-transform duration-500 hover:scale-105 hover:rotate-3">
        <img src="{{asset('asset/img/banner.png')}}"
             alt="Banner Image"
             class="w-full ">
        <!-- Soft glow overlay -->
        <div class="absolute top-0 left-0 w-full h-full rounded-3xl bg-gradient-to-r from-transparent to-blue-100 opacity-20 pointer-events-none"></div>
      </div>
    </div>
  </section>

{{-- About us --}}

<section class="bg-gray-50 py-20 px-6 lg:px-20">
    <div class="max-w-7xl mx-auto">
      <!-- Heading -->
      <div class="text-center mb-16">
        <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">About Our Billing Software</h2>
        <p class="text-gray-600 text-lg lg:text-xl max-w-2xl mx-auto">
          We provide a powerful, smart, and easy-to-use billing solution that empowers businesses to manage invoices, payments, and customer accounts seamlessly. Our platform is designed for small businesses, startups, and enterprises alike.
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">

        <!-- Feature 1 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 flex flex-col items-center text-center">
          <img src="https://cdn.pixabay.com/photo/2025/07/01/12/47/ai-generated-9690635_1280.png" alt="" class="w-24 h-24 mb-4">
          <h3 class="text-2xl font-semibold text-gray-900 mb-2">Fast & Efficient</h3>
          <p class="text-gray-600">
            Automate your billing and invoicing process with speed and precision, saving time and reducing errors.
          </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 flex flex-col items-center text-center">
          <img src="https://cdn.pixabay.com/photo/2025/01/06/11/35/list-9314591_1280.png" alt="" class="w-24 h-24 mb-4">
          <h3 class="text-2xl font-semibold text-gray-900 mb-2">Secure & Reliable</h3>
          <p class="text-gray-600">
            Keep your financial data safe with our secure, GDPR-compliant system that ensures complete confidentiality.
          </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 flex flex-col items-center text-center">
           <img src="https://cdn.pixabay.com/photo/2024/06/13/17/33/graph-8828099_1280.png" alt="" class="w-24 h-24 mb-4">
          <h3 class="text-2xl font-semibold text-gray-900 mb-2">Analytics & Insights</h3>
          <p class="text-gray-600">
            Gain actionable insights from detailed reports and analytics to optimize your financial performance.
          </p>
        </div>

        <!-- Feature 4 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 flex flex-col items-center text-center">
          <img src="https://cdn.pixabay.com/photo/2016/06/15/16/16/man-1459246_1280.png" alt="" class="w-24 h-24 mb-4">
          <h3 class="text-2xl font-semibold text-gray-900 mb-2">Customizable</h3>
          <p class="text-gray-600">
            Tailor invoices, templates, and workflows according to your business needs for a personalized experience.
          </p>
        </div>

        <!-- Feature 5 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 flex flex-col items-center text-center">
          <img src="https://cdn.pixabay.com/photo/2019/07/10/13/03/people-4328648_1280.png" alt="" class="w-24 h-24 mb-4">
          <h3 class="text-2xl font-semibold text-gray-900 mb-2">User-Friendly</h3>
          <p class="text-gray-600">
            Simple and intuitive interface that allows anyone on your team to manage finances without training.
          </p>
        </div>

        <!-- Feature 6 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 flex flex-col items-center text-center">
        <img src="https://cdn.pixabay.com/photo/2022/01/13/08/59/phone-support-6934722_1280.png" alt="" class="w-24 h-24 mb-4">
          <h3 class="text-2xl font-semibold text-gray-900 mb-2">24/7 Support</h3>
          <p class="text-gray-600">
            Our expert support team is available around the clock to help you with any questions or issues.
          </p>
        </div>

      </div>
    </div>
  </section>

@endsection
