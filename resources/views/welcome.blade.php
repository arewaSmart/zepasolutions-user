@extends('layouts.index')
@section('title','Zepa Solutions - Easy Verifications for your Business')
@section('content')
  <!-- ============== Start Hero section ========== -->
  <section class="hero d-flex flex-column justify-content-center align-items-center mt-5 pt-5" id="hero">
    <div id="particles-js"></div>
    <div class="container">
      <div class="row justify-content-center align-items-center">
        <div class="hero-text col-12 col-lg-5">
          <div class="row">
            <div class="col-12 ">
              <h1 class="title col-12" data-aos="fade-up" data-aos-delay="150">Zepa  <span
                  class="unique-text">Easy Way of making payment</span> for your Business </h1>
            </div>
            <div class="col-lg-10 col-12" data-aos="fade-up" data-aos-delay="200">
              <p>
              Discover Zepa – the effortless way to make and receive payments,
              empowering your business with seamless, secure, and hassle-free transactions.
              </p>
            </div>
            <div class="col-10" data-aos="fade-up" data-aos-delay="250">
              <a href="{{ route('login') }}" class="btn">Join Now</a>
              <a href="{{ URL('download-app')}}" class="btn">download App</a>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-7 mx-md-auto text-center" data-aos="fade-left" data-aos-delay="100">
          <div class="hero-image">
            <div class="hero-img">
              <img class="img-fluid " alt="hero-img"
                src="{{ asset('assets/home/images/logo/man-yellow.png')}}">
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ============== End Hero section ========== -->

  <!-- ============== Start services section ========== -->
  <section class="container services py-5" id="services">
    <div class="heading">
      <h4 class="pretitle" data-aos="fade-up">
        our services
      </h4>
      <h1 class="title col-lg-10 col-12" data-aos="fade-up" data-aos-delay="100">
        What We’re Offering?
      </h1>
      <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
      with a growing number of young entrepreneurs and tech-savvy residents. That's why we
       prioritize strong and reliable network coverage throughout Nigeria and the surrounding.
      </p>
    </div>
    <div class="row gy-4 gx-4 ">
      <!-- service number 1 -->
      <div class="col-md-6 col-12 col-lg-4 mx-auto ">
        <div class="box box-service box-hover" data-aos="fade-right" data-aos-delay="250">
          <div class="box-icon my-2">
            <i class="bi bi-coin"></i>
          </div>
          <h2 class="title-2 my-2 ">Airtime and Data </h2>
          <p>Ditch the dropped calls! Get connected that truly matters. We're more
            than just telecom, we're your connection champions.
          </p>
        </div>
      </div>
      <div class=" col-md-6 col-lg-4 mx-auto" data-aos="fade-down" data-aos-delay="200">
        <!-- service number 2 -->
        <div class="box box-service box-hover">
        <div class="box-icon my-2">
        <i class="bi bi-question-octagon-fill"></i>
          </div>
          <h2 class="title-2 my-2 ">Identity Verifications</h2>
          <p>Trust is key. Verify your identity for a smooth experience.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 mx-auto" data-aos="fade-left" data-aos-delay="250">
        <!-- service number 3 -->
        <div class="box box-service  box-hover">
          <div class="box-icon my-2">
           <i class="bi bi-globe2"></i>
          </div>
          <h2 class="title-2 my-2 ">Bill payment</h2>
          <p>Don't miss out! Timely bill payments unlock rewards,
           Peace of mind guaranteed: Pay your bills easily with us</p>
        </div>
      </div>
    </div>
  </section>
  <!-- ============== End services section ========== -->

  <!-- ============== Start About section ========== -->
  <section class="about py-5 mt-5" id="about">
    <div class="container">
      <div class="row mt-5 justify-content-center align-items-center">
        <div class="col-12 col-lg-6">
          <h4 class="pretitle" data-aos="fade-up" data-aos-delay="200">
            about us
          </h4>
          <h1 class="title col-12" data-aos="fade-up" data-aos-delay="250">
            We are the Best Business Solutions since <span class="unique-text">2015</span>
          </h1>
          <p class="col-lg-10 col-12" data-aos="fade-up" data-aos-delay="300">
           We make it easy to top up airtime, data, and cable subscriptions, while also providing secure identity verification,
           Effortless top-ups and secure identity checks. Zepa: Your one-stop shop for digital essentials,
           Stay connected, verified, and entertained. Zepa - Your reliable partner for airtime, data, cable, and ID solutions.
          </p>
          <a href="{{ route('login') }}" class="btn" data-aos="fade-up" data-aos-delay="350">join now</a>
        </div>
        <!-- image -->
        <div class="col-12 col-lg-6 d-flex align-items-center" data-aos="fade-left" data-aos-delay="100">
          <img src="{{ asset('assets/home/images/logo/about.png')}}" alt="about-img">
        </div>
      </div>
      <div class="row mt-5 py-4 justify-content-center align-items-center">
        <!-- image -->
        <div class="col-12 col-lg-6 d-flex align-items-center" data-aos="fade-right" data-aos-delay="100">
          <img src="{{ asset('assets/home/images/logo/pop.png')}}" alt="about-img">
        </div>
        <div class="col-12 col-lg-6">
          <h1 class="title col-12" data-aos="fade-up" data-aos-delay="200">
            We Deal With The Aspects Of telecoms <span class="unique-text"> Services</span>
          </h1>
          <p class="col-lg-10 col-12" data-aos="fade-up" data-aos-delay="250">
            Stay connected and verified with Zepa. Your trusted partner for convenient airtime, data, cable, product and services, and ID solutions.
          </p>
          <div class="row gx-2 gy-2 col-12">
            <div class="col-lg-6 col-md-6 col-12" data-aos="fade-up" data-aos-delay="300">
              <div class="box box-hover">
                <i class="bi bi-map-fill"></i>
                <h5 class="mx-4 title-2 fw-bold">Worldwide Verifications</h5>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12" data-aos="fade-up" data-aos-delay="350">
              <div class="box box-hover">
                <i class="bi bi-headset"></i>
                <h5 class="mx-4 title-2 fw-bold">Support 24h/7</h5>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12" data-aos="fade-up" data-aos-delay="400">
              <div class="box box-hover">
                <i class="bi bi-controller"></i>
                <h5 class="mx-4 title-2  fw-bold">Easy To Reach</h5>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12" data-aos="fade-up" data-aos-delay="450">
              <div class="box box-hover">
                <i class="bi bi-star-fill"></i>
                <h5 class="mx-4 title-2 fw-bold">Fast and Realable</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-5 py-4 justify-content-center align-items-center">
        <div class="col-12 col-lg-6">
          <h1 class="title col-12" data-aos="fade-up" data-aos-delay="200">
        Our company has been there for 20years of <span class="unique-text">Experience</span>
          </h1>
          <p class="col-lg-10 col-12" data-aos="fade-up" data-aos-delay="250">
           Never run out of airtime or data again! Zepa makes refilling and managing your digital needs simple and secure.
          Stay connected and verified with Zepa.
          </p>
          <ul class="col-12 bars">
            <li class="py-3" data-aos="fade-up" data-aos-delay="300">
              <div class="justify-content-between d-flex">
                <h5>Airtime</h5>
                <h6>99%</h6>
              </div>
              <div class="progress-bar">
                <div class="progress-bar-fill progress-bar-fill-1 "></div>
              </div>
            </li>
            <li class="py-3" data-aos="fade-up" data-aos-delay="350">
              <div class="justify-content-between d-flex">
                <h5>Data</h5>
                <h6>99%</h6>
              </div>
              <div class="progress-bar">
                <div class="progress-bar-fill progress-bar-fill-2 "></div>
              </div>
            </li>
            <li class="py-3" data-aos="fade-up" data-aos-delay="400">
              <div class="justify-content-between d-flex">
                <h5>identity Verifications</h5>
                <h6>99%</h6>
              </div>
              <div class="progress-bar">
              <div class="progress-bar-fill progress-bar-fill-2 "></div>
           </div>
              </li>
            <li class="py-3" data-aos="fade-up" data-aos-delay="400">
              <div class="justify-content-between d-flex">
                <h5>Send and recieve Monie</h5>
                <h6>99%</h6>
              </div>
              <div class="progress-bar">
                <div class="progress-bar-fill progress-bar-fill-3 "></div>
              </div>
            </li>
          </ul>
        </div>
        <!-- image -->
        <div class="col-12 col-lg-6 d-flex align-items-center" data-aos="fade-left" data-aos-delay="100">
          <img src="{{ asset('assets/home/images/logo/expert.png')}}" alt="about-img">
        </div>
      </div>

    </div>
  </section>
  <!-- ============== End About section ========== -->

  <!-- ============== Start numbers section ========== -->
  <section class="counter-up my-5" data-aos="fade-up" data-aos-delay="100">
    <div class="container">
      <div class="row">
        <div class="col-lg-3  col-12 mt-5 mt-lg-0" data-aos="fade-right" data-aos-delay="200">
          <i class="ri-discuss-line icon"></i>
          <h1 class="counter">21,000</h1>
          <h2 class="title-2">happy clients</h2>
        </div>
        <div class="col-lg-3 col-12 mt-5 mt-lg-0" data-aos="fade-up" data-aos-delay="250">
          <i class="ri-discuss-line icon"></i>
          <h1 class="counter">6,2087</h1>
          <h2 class="title-2">Success</h2>
        </div>
        <div class="col-lg-3 col-12 mt-5 mt-lg-0" data-aos="fade-down" data-aos-delay="250">
          <i class="ri-discuss-line icon"></i>
          <h1 class="counter">108.0</h1>
          <h2 class="title-2">failed</h2>
        </div>
        <div class="col-lg-3 col-12 mt-5 mt-lg-0" data-aos="fade-left" data-aos-delay="200">
          <i class="ri-discuss-line icon"></i>
          <div class="counter">1,099,87</div>
          <div class="title-2">Number Of Request</div>
        </div>
      </div>
    </div>
  </section>
  <!-- ============== End numbers section ========== -->

  <!-- ============== Start Why us section ========== -->
  <section class="container why-choose-us py-5">
    <div class="heading">
      <h4 class="pretitle" data-aos="fade-up">
        Why choose us
      </h4>
      <h1 class="title col-12" data-aos="fade-up" data-aos-delay="100">
        why our clients choose us
      </h1>
      <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
        All-in-one Convenience: Manage your airtime, data, cable subscriptions,
        and even verify your identity – all through Zepa! No need to juggle multiple apps or services.
      </p>
    </div>
    <div class="row  gy-4 gx-4 ">
      <div class="col-md-6 col-lg-4 mx-auto" data-aos="fade-right" data-aos-delay="250">
        <div class="box">
          <h1 class="my-4">01.</h1>
          <h2 class="title-2 my-2 ">Effortless Top-Ups</h2>
          <p>Never get caught with low airtime or data again.
            Zepa makes refilling quick and easy, keeping you connected without the hassle.
         </p>
        </div>
      </div>
      <div class="col-md-6  col-lg-4 mx-auto" data-aos="fade-up" data-aos-delay="200">
        <div class="box">
          <h1 class="my-4">02.</h1>
          <h2 class="title-2 my-2 ">Rock-Solid Security</h2>
          <p>Zepa prioritizes secure transactions and identity verification.
             You can trust your information is protected while you manage your digital needs.</p>
        </div>
      </div>
      <div class="col-md-6  col-lg-4 mx-auto" data-aos="fade-left" data-aos-delay="250">
        <div class="box">
          <h1 class="my-4">03.</h1>
          <h2 class="title-2 my-2 ">Save Time & Money</h2>
          <p>Streamline your digital life with Zepa. Save time by managing everything in one place and potentially find better
             deals on top-ups and subscriptions compared to managing them individually.</p>
        </div>
      </div>
    </div>
  </section>
  <!-- ============== End Why us section ========== -->



  <!-- ============== Start FAQ section ========== -->
  <section class="container faq py-5">
    <div class="heading">
      <h4 class="pretitle" data-aos="fade-up">
        FAQ
      </h4>
      <h1 class="title col-12" data-aos="fade-up" data-aos-delay="100">
        Frequently Asked Questions
      </h1>
      <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
      To help you find the information you need quickly, we've compiled a list of Frequently Asked Questions
      For your convenience, we've answered some of our most common questions below
      </p>
    </div>
    <div class="row justify-content-center align-items-center  gx-4 ">
      <div class="col-12 col-lg-6" data-aos="fade-right" data-aos-delay="200">
        <img src="{{ asset('assets/home/images/logo/faq.png')}}" alt="faq">
      </div>
      <div class="col-12 col-lg-6  ">
        <div class="col-12 my-4" data-aos="fade-up" data-aos-delay="250">
          <div class="box">
            <div class="d-flex w-100 justify-content-between">
              <a data-bs-toggle="collapse" class="w-100" onclick="rotateIcon('icon1')" href="#answer1" role="button"
                aria-expanded="false">
                <h4 class="d-flex justify-content-between w-100 heading-3 m-0 p-0">
                  How Long it will take to receive my data ?
                  <i class="bi bi-chevron-compact-down mx-4 rotate-icon" id="icon1"></i>
                </h4>
              </a>
            </div>
            <p id="answer1" class="collapse">
              Our services is automated, it will take like 10 to 15 seconds to recieve your
              airtime if the transactions is Successful. and if the transaction is not Successful your wallet will not be debited
            </p>
          </div>
        </div>
        <div class="col-12 my-4 " data-aos="fade-up" data-aos-delay="300">
          <div class="box">
            <div class="d-flex w-100 justify-content-between">
              <a data-bs-toggle="collapse" class="w-100" onclick="rotateIcon('icon2')" href="#answer2" role="button"
                aria-expanded="false">
                <h4 class="d-flex justify-content-between w-100 heading-3 m-0 p-0">
                 How Long self services Modification will take ?
                  <i class="bi bi-chevron-compact-down mx-4 rotate-icon" id="icon2"></i>
                </h4>
              </a>
            </div>
            <p id="answer2" class="collapse">
             the self service also is automated your request is directly sending to the team for proper
             resolutions, and it may take upto 72h.
            </p>
          </div>
        </div>
        <div class="col-12 my-4 " data-aos="fade-up" data-aos-delay="350">
          <div class="box">
            <div class="d-flex w-100 justify-content-between">
              <a data-bs-toggle="collapse" class="w-100" onclick="rotateIcon('icon3')" href="#answer3" role="button"
                aria-expanded="false">
                <h4 class="d-flex justify-content-between w-100 heading-3 m-0 p-0">
                  Is There Any Updates after sending the request ?
                  <i class="bi bi-chevron-compact-down mx-4 rotate-icon" id="icon3"></i>
                </h4>
              </a>
            </div>
            <p id="answer3" class="collapse">
              Yes sure! we will send you an email concerning your request in each and every steps
            </p>
          </div>
        </div>
        <div class="col-12 my-4" data-aos="fade-up" data-aos-delay="400">
          <div class="box">
            <div class="d-flex w-100 justify-content-between">
              <a data-bs-toggle="collapse" class="w-100" onclick="rotateIcon('icon4')" href="#answer4" role="button"
                aria-expanded="false">
                <h4 class="d-flex justify-content-between w-100 heading-3 m-0 p-0">
                  How Much For The Service?
                  <i class="bi bi-chevron-compact-down mx-4 rotate-icon" id="icon4"></i>
                </h4>
              </a>
            </div>
            <p id="answer4" class="collapse">
              We have made it easy for you with the lowest chargies, and also we offer 20% discunt on the first five transactions.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ============== End FAQ section ========== -->

  <!-- ============== Start contact section ========== -->
  <section class="container contact py-5" id="contact">
    <div class="heading ">
      <h4 class="pretitle" data-aos="fade-up">
        contact
      </h4>
      <h1 class="title col-12" data-aos="fade-up" data-aos-delay="100">
        contact us for Any Questions
      </h1>
      <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
      We encourage you to reach out to our support team with any questions you may have
      </p>
    </div>
    <div class="row  gx-4 ">
      <div class="col-12 col-lg-6 gy-3">
        <h2 class="title-2 " data-aos="fade-right" data-aos-delay="200">
          contact info :
        </h2>
        <div class="info d-flex my-4 " data-aos="fade-right" data-aos-delay="250">
          <h5><i class="bi bi-envelope-fill mx-4"></i>support@zepasolutions.com</h5>
        </div>
        <div class="info d-flex my-4 " data-aos="fade-up" data-aos-delay="300">
          <h5><i class="bi bi-phone-fill mx-4"></i>+2348012345678</h5>
        </div>
        <div class="info d-flex my-4" data-aos="fade-up" data-aos-delay="400">
        <h5>
        <a href="https://www.facebook.com/profile.php?id=61563505366382&mibextid=ZbWKwL" target="_blank">
      <i class="bi bi-facebook mx-4"></i>@zepa wallet</a>
    </div>
        <div class="info d-flex my-4" data-aos="fade-up" data-aos-delay="400"><h5>
        <a href="https://wa.me/+2348012345678" target="_blank">
      <i class="bi bi-whatsapp mx-4"></i>+2348012345678
    </a>
      </div>
      </div>
      <div class="col-12 col-lg-6">
        <!--Form To have user messages-->
        <form class="main-form" id="contact-us-form" action="#" method="post">
          <div class="col-12">
            <div class="row g-3 mb-1">
              <div class="col-lg-6 col-12" data-aos="fade-right" data-aos-delay="200">
                <input placeholder="name" type="text" id="name" name="name" required class="text-input">
              </div>
              <div class="col-lg-6 col-12" data-aos="fade-left" data-aos-delay="200">
                <input placeholder="subject" type="text" id="subject" name="subject" required class="text-input">
              </div>
            </div>
          </div>
          <div class="col-12" data-aos="fade-up" data-aos-delay="250">
            <input placeholder="email" type="email" id="email" name="email" required class="text-input my-2">
          </div>
          <div class="col-12" data-aos="fade-up" data-aos-delay="300">
            <textarea placeholder="message" class="text-input my-2" rows="7" cols="30" id="message" name="message"
              required></textarea>
          </div>
          <div class="col-12" data-aos="fade-up" data-aos-delay="350">
            <button type="submit" value="Submit" class="btn">send now</button>
          </div>
        </form>
      </div>
    </div>
  </section>
  @endsection
