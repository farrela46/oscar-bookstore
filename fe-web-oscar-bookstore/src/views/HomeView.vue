<template>
  <main>
    <Navbar /> <!-- Include the Navbar component -->
    <div class="gradient-container">
      <!-- Use a wrapper div for scroll snapping -->
      <div class="scroll-wrapper" ref="scrollWrapper">
        <section class="welcome-section">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6 d-flex justify-content-center align-items-center">
                <div class="text-left align-middle">
                  <h1 class="cursive mb-6" style="font-weight: 600; font-size: 2.5rem;">
                    SELAMAT DATANG
                    <br>
                    di TOKO BUKU OSCAR
                  </h1>
                  <p>Kami memiliki lebih dari ratusan buku untuk dijelajahi</p>
                  <button class="btn btn-primary" @click="scrollToNextSection">Jelajahi Kami</button>
                </div>
              </div>
              <!-- Right column for the image -->
              <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="mt-5">
                  <img src="../assets/LandingPage/Books.png" style="width:500px;" class="rounded img-fluid" alt="...">
                </div>
              </div>
            </div>
          </div>
        </section>
        <section class="next-section">
          <div class="container">
            <div class="row d-flex justify-content-center align-items-center">
              <div class="col-md-6 my-auto">
                <div class="d-flex justify-content-between">
                  <div class="image-frame">
                    <img src="../assets/LandingPage/oscar1.jpg" class="rounded" alt="Oscar">
                  </div>
                  <div class="image-frame d-flex justify-content-center">
                    <img src="../assets/LandingPage/oscar2.jpg" class="rounded" alt="Oscar">
                  </div>
                </div>
              </div>
              <!-- Right column for the image -->
              <div class="col-md-6">
                <h1>Toko Buku Terlengkap dan Terjangkau di kawasan Kediri</h1>
                <p>Kami menyediakan buku -buku mulai dari buku pengetahuan, buku cerita, buku resep makanan,
                  komik, majalah, hingga ratusan buku lainnya dengan stok yang banyak
                </p>
                <ul class="justify-content-start" style="list-style: none;">
                  <li class="py-2">
                    <span class="list-icon"><img src="https://i.ibb.co/Tcwn0PN/service-list.png"
                        alt="service-list"></span>
                    <p class="my-auto m-2" style="display: inline-block; vertical-align: middle;">Harga
                      Murah!</p>
                  </li>
                  <li class="py-2">
                    <span class="list-icon"><img src="https://i.ibb.co/Tcwn0PN/service-list.png"
                        alt="service-list"></span>
                    <p class="my-auto m-2" style="display: inline-block; vertical-align: middle;">Stok
                      Selalu Tersedia</p>
                  </li>
                  <li class="py-2">
                    <span class="list-icon"><img src="https://i.ibb.co/Tcwn0PN/service-list.png"
                        alt="service-list"></span>
                    <p class="my-auto m-2" style="display: inline-block; vertical-align: middle;">
                      Layanan
                      Antar</p>
                  </li>
                  <li class="py-2">
                    <span class="list-icon"><img src="https://i.ibb.co/Tcwn0PN/service-list.png"
                        alt="service-list"></span>
                    <p class="my-auto m-2" style="display: inline-block; vertical-align: middle;">
                      Pembayaran
                      bisa via COD</p>
                  </li>
                  <li class="py-2">
                    <span class="list-icon"><img src="https://i.ibb.co/Tcwn0PN/service-list.png"
                        alt="service-list"></span>
                    <p class="my-auto m-2" style="display: inline-block; vertical-align: middle;">
                      Garansi 1
                      bulan</p>
                  </li>
                </ul>

              </div>
            </div>
          </div>
        </section>
        <section class="third-section flex-wrap d-flex justify-content-center align-items-center">
          <div class="container">
            <div class="row">
              <div>
                <h1 class="bold">Why Us?</h1>
              </div>
              <div>
                <div class="row w-100">
                  <div class="col-md-6 col-lg-3 ">
                    <div class="card" style="width: 16.75rem; height: 12rem;">
                      <div class="card-body">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                          class="bi bi-book mb-2" viewBox="0 0 16 16">
                          <path
                            d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
                        </svg>
                        <h5>Buku Lengkap</h5>
                        <p>Tersedia banyak pilihan buku untuk di eksplor</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <div class="card" style="width: 16.75rem; height: 12rem;">
                      <div class="card-body">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                          class="bi bi-tag mb-2" viewBox="0 0 16 16">
                          <path d="M6 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0z" />
                          <path
                            d="M2 1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 1 6.586V2a1 1 0 0 1 1-1zm0 5.586 7 7L13.586 9l-7-7H2v4.586z" />
                        </svg>
                        <h5>Harga Murah</h5>
                        <p class="card-detail-list">Harga murah dan bersaing, bisa bandingkan harga
                          kami
                          dengan
                          toko buku lain</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <div class="card" style="width: 16.75rem; height: 12rem;">
                      <div class="card-body">

                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                          class="bi bi-clock mb-2" viewBox="0 0 16 16">
                          <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z" />
                          <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z" />
                        </svg>
                        <h5>Layanan 24 Jam</h5>
                        <p>Siap melayani kebutuhan Anda selama 24 jam nonstop. Kami
                          juga tersedia di akhir minggu</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <div class="card" style="width: 16.75rem; height: 12rem;">
                      <div class="card-body">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                          class="bi bi-award mb-2" viewBox="0 0 16 16">
                          <path
                            d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z" />
                          <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z" />
                        </svg>
                        <h5>Buku Berkualiatas</h5>
                        <p class="card-detail-list">Buku yang dijual selalu dalam kondisi bagus
                          dan berkualitas
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div>
                  <h1 class="text-right">Find Us on</h1>
                </div>
                <div class="row">
                  <div class="col-sm-4">
                    <iframe
                      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3952.7926225546043!2d112.02894431093304!3d-7.81176419217626!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e785769dee1ffc1%3A0x8841f2f6bd62b46d!2sToko%20Buku%20Oscar!5e0!3m2!1sid!2sid!4v1696152777230!5m2!1sid!2sid"
                      width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy"
                      referrerpolicy="no-referrer-when-downgrade"></iframe>
                  </div>
                  <div class="col-sm-8">

                    <h3>Toko Buku Oscar </h3>
                    <p>terletak di Jalan Ahmad Yani No. 85, Kecamatan Ngasem, Kabupaten Kediri, Provinsi
                      Jawa Timur</p>

                    <p>Connect with us</p>
                    <ul class="list-unstyled">
                      <li class="d-flex align-items-center">
                        <img src="https://i.ibb.co/3TQ1Bgh/icon-facebook.png" alt="icon-facebook" border="0" class="mx-2">
                        Facebook
                      </li>
                      <li class="mt-2 d-flex align-items-center">
                        <img src="https://i.ibb.co/TqJsYch/icon-instagram.png" alt="icon-instagram" border="0"
                          class="mx-2">
                        Instagram
                      </li>
                      <li class="mt-2 d-flex align-items-center">
                        <img src="https://i.ibb.co/Dfw5cLB/icon-twitter.png" alt="icon-twitter" border="0" class="mx-2">
                        Twitter
                      </li>
                      <li class="mt-2 d-flex align-items-center">
                        <img src="https://i.ibb.co/tY0TB5Z/icon-mail.png" alt="icon-mail" border="0" class="mx-2">
                        Email
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>
</template>
<script>
import Navbar from '@/components/HomeNavbar.vue';
export default {
  name: 'HomePage',
  components: {
    Navbar,
  },
  methods: {
    scrollToNextSection() {
      // Scroll to the next section using JavaScript
      const nextSection = this.$refs.scrollWrapper.querySelector('.next-section');
      if (nextSection) {
        nextSection.scrollIntoView({ behavior: 'smooth' });
      }
    },
  },
  mounted() {
    // Add event listeners for scroll snapping
    const scrollWrapper = this.$refs.scrollWrapper;
    if (scrollWrapper) {
      scrollWrapper.addEventListener('scroll', this.handleScroll);
    }
  },
  beforeDestroy() {
    // Remove event listener to prevent memory leaks
    const scrollWrapper = this.$refs.scrollWrapper;
    if (scrollWrapper) {
      scrollWrapper.removeEventListener('scroll', this.handleScroll);
    }
  },
};
</script>
<style scoped>
@import url(https://fonts.googleapis.com/css?family=Questrial);
@import url(https://fonts.googleapis.com/css?family=Mooli);

.cursive {
  font-family: 'Mooli', sans-serif;
  text-transform: none;
}

.gradient-container {
  background-image: url('../assets/LandingPage/Background.png');
  background-size: cover;
}

.scroll-wrapper {
  scroll-snap-type: y proximity;
  overflow-y: scroll;
  height: 100vh;
  scroll-behavior: smooth;
}

.welcome-section,
.next-section {
  height: 100vh;
  scroll-snap-align: start;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.third-section {
  padding: 20px;
  height: 100vh;
}

.third-section .col-md-6 {
  margin-bottom: 20px;
}


.third-section {
  scroll-snap-align: none;
}

.image-frame {
  width: 48%;
  overflow: hidden;
  position: relative;
  border-radius: 10px;
}

.image-frame img {
  width: 100%;
  height: auto;
  object-fit: cover;
}
</style>
  
