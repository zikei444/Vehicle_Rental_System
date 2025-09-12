<!-- 
STUDENT NAME: LIEW ZI KEI 
STUDENT ID: 23WMR14570
-->

@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- Welcome Section --}}
    <section class="text-center mb-5 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s;">
        <h1 class="display-4 fw-bold">Welcome to Fantasy Rentals</h1>
        <p class="lead mt-3">Explore our magical fleet of vehicles ready to transport you anywhere in style!</p>
        <a href="{{ route('vehicles.index') }}" class="btn btn-primary btn-lg mt-3 shadow-sm">
            Explore Vehicles
        </a>
    </section>

    {{-- Vision & Mission --}}
    <section class="mb-5 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s 0.2s;">
        <h2 class="text-center mb-3">Our Vision & Mission</h2>
        <div class="row g-4 text-center">
            <div class="col-md-6">
                <div class="p-4 shadow-sm rounded bg-light">
                    <h5 class="fw-bold">Vision</h5>
                    <p>To become the most trusted and magical vehicle rental service, delivering memorable journeys to every customer.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-4 shadow-sm rounded bg-light">
                    <h5 class="fw-bold">Mission</h5>
                    <p>Providing safe, comfortable, and diverse vehicles with exceptional service, making travel easy and fun for all adventurers.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Achievements --}}
    <section class="mb-5 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s 0.4s;">
        <h2 class="text-center mb-4">Our Achievements</h2>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 shadow-sm rounded bg-light">
                    <h3 class="fw-bold">500+</h3>
                    <p>Happy Customers</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 shadow-sm rounded bg-light">
                    <h3 class="fw-bold">1000+</h3>
                    <p>Vehicles Served</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 shadow-sm rounded bg-light">
                    <h3 class="fw-bold">10 Years</h3>
                    <p>Experience in Rentals</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Vehicles Section --}}
    <section class="row text-center g-4 mb-5">
        <h2 class="text-center mb-4 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s 0.6s;">Our Fleet</h2>

        {{-- Car --}}
        <div class="col-md-4 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s 0.8s;">
            <div class="card shadow-sm p-3 h-100">
                <img src="{{ asset('images/car.png') }}" class="card-img-top mb-3" alt="Car">
                <h5 class="card-title">Car</h5>
                <p class="card-text">Comfortable and fast for city rides and weekend adventures.</p>
            </div>
        </div>

        {{-- Van --}}
        <div class="col-md-4 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s 1s;">
            <div class="card shadow-sm p-3 h-100">
                <img src="{{ asset('images/van.png') }}" class="card-img-top mb-3" alt="Van">
                <h5 class="card-title">Van</h5>
                <p class="card-text">Spacious and reliable, perfect for family trips and group journeys.</p>
            </div>
        </div>

        {{-- Truck --}}
        <div class="col-md-4 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s 1.2s;">
            <div class="card shadow-sm p-3 h-100">
                <img src="{{ asset('images/truck.png') }}" class="card-img-top mb-3" alt="Truck">
                <h5 class="card-title">Truck</h5>
                <p class="card-text">Heavy-duty vehicles for transporting goods or large equipment safely.</p>
            </div>
        </div>
    </section>

    {{-- Closing Paragraph --}}
    <section class="text-center mb-5 reveal" style="opacity:0; transform: translateY(50px); transition: all 0.8s 1.4s;">
        <h4>Ready for Your Next Adventure?</h4>
        <p>Our fleet is always prepared for your journeys. Whether it’s a short trip in the city or a long road adventure, Fantasy Rentals has the perfect ride for you.</p>
        <a href="{{ route('vehicles.index') }}" class="btn btn-primary btn-lg mt-3 shadow-sm">
            View All Vehicles
        </a>
    </section>

</div>

{{-- Scroll Reveal Script --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    const reveals = document.querySelectorAll('.reveal');

    function reveal() {
        for (let i = 0; i < reveals.length; i++) {
            const windowHeight = window.innerHeight;
            const elementTop = reveals[i].getBoundingClientRect().top;
            const revealPoint = 150;

            if(elementTop < windowHeight - revealPoint) {
                reveals[i].style.opacity = 1;
                reveals[i].style.transform = 'translateY(0)';
            }
        }
    }

    window.addEventListener('scroll', reveal);
    reveal(); 
});
</script>
@endsection
