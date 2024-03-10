<script setup>
import { onMounted, onBeforeMount, onBeforeUnmount, ref } from "vue";
import { useStore } from "vuex";
import AppFooter from "@/examples/Footer.vue";
import Navbar from "@/examples/PageLayout/HomeNavbar.vue";
import setTooltip from "@/assets/js/tooltip.js";
// import Typed from 'typed.js';
const body = document.getElementsByTagName("body")[0];

const store = useStore();

// document.addEventListener('DOMContentLoaded', function () {
//   const typedStrings = document.getElementById('typed-strings').getElementsByTagName('h1');
//   const typedOptions = {
//     strings: Array.from(typedStrings).map(el => el.innerText),
//     typeSpeed: 100,
//     backSpeed: 50,
//     loop: true
//   };

//   new Typed('#typed', typedOptions);
// });

const images = ref([
  { link: require("@/assets/img/oscar0.png") },
  { link: require("@/assets/img/oscar1.png") },
  { link: require("@/assets/img/oscar2.png") }
]);


onMounted(() => {
  setTooltip();

});

onBeforeMount(() => {
  store.state.layout = "vr";
  store.state.showNavbar = false;
  store.state.showSidenav = false;
  store.state.showFooter = false;
  body.classList.add("virtual-reality");
  store.state.isTransparent = "bg-white";
});
onBeforeUnmount(() => {
  store.state.layout = "default";
  store.state.showNavbar = true;
  store.state.showSidenav = true;
  store.state.showFooter = true;
  body.classList.remove("virtual-reality");

  if (store.state.isPinned === false) {
    const sidenav_show = document.querySelector(".g-sidenav-show");
    sidenav_show.classList.remove("g-sidenav-hidden");
    sidenav_show.classList.add("g-sidenav-pinned");
    store.state.isPinned = true;
  }
  store.state.isTransparent = "bg-transparent";
});
</script>

<template>
  <div class="container top-0 position-sticky z-index-sticky">
    <div class="row">
      <div class="col-12">
        <navbar isBlur="blur  border-radius-lg my-3 py-2 start-0 end-0 mx-4 shadow" v-bind:darkMode="true" />
      </div>
    </div>
  </div>
  <div class="mx-3 mt-2 position-relative" :style="{
          backgroundImage: 'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5) ) ,url(' + require('@/assets/img/library.png') + ')',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          height: '65vh',
          borderRadius: '30px 30px 0 0'
        }">
    <div class="container-fluid h-100">
      <div class="row h-100 justify-content-center align-items-center">
        <div class="col-auto text-center">
          <h3 class="text-white">Welcome to</h3>
          <h1 class="text-white">TOKO BUKU OSCAR</h1>
        </div>
      </div>
    </div>
  </div>
  <main class="main-content mt-5">
    <div class="section min-vh-85 position-relative">
      <div class="container-fluid">
        <div class="row mx-5 pt-4 mb-4">
          <div class="col-md-4 position-relative">
            <div class="text-center">
              <h1 class="text-gradient text-success"><span>100</span>+</h1>
              <h5 class="mt-3">Buku tersedia</h5>
              <p class="text-sm font-weight-normal">Buku yang tersedia di Toko Buku Oscar</p>
            </div>
            <hr class="vertical dark">
          </div>
          <div class="col-md-4 position-relative">
            <div class="text-center">
              <h1 class="text-gradient text-success"><span>12</span>+</h1>
              <h5 class="mt-3">Kategori Buku</h5>
              <p class="text-sm font-weight-normal">Toko Buku Oscar menjual berbagai kategori buku.</p>
            </div>
            <hr class="vertical dark">
          </div>
          <div class="col-md-4 position-relative">
            <div class="text-center">
              <h1 class="text-gradient text-success"><span>70</span>+</h1>
              <h5 class="mt-3">Pengguna Aktif</h5>
              <p class="text-sm font-weight-normal">Pengguna aktif YaDipinjam</p>
            </div>
          </div>
        </div>
      </div>
      <br>
      <div class="mx-3 mt-4 mb-5" :style="{
          backgroundColor: 'white',
          backgroundPosition: 'center',
          borderRadius: '30px 30px 30px 30px'
        }">
        <div class="row h-100 justify-content-center align-items-center m-5">
          <div class="col-auto text-center m-2">
            <h2 class="text-dark mb-4 mt-5 pb-5">Toko Buku Oscar Kediri</h2>
            <div class="row">
              <div class="col-md-6 ">
                <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div v-for="(image, index) in images" :key="index"
                      :class="['carousel-item', { active: index === 0 }]">
                      <img :src="image.link" class="d-block w-100" :alt="'Slide ' + (index + 1)"
                        style="max-width: 400px; object-fit: cover; border-radius: 30px;">
                    </div>
                  </div>
                  <div class="carousel-indicators">
                    <button v-for="(image, index) in images" :key="index" type="button"
                      :data-bs-target="'#carouselExampleIndicators'" :data-bs-slide-to="index"
                      :class="['thumbnail', { 'first-two': index === 0 || index === 1 }, { active: index === 0 }]"
                      aria-label="'Slide ' + (index + 1)">
                      <img :src="image.link" class="d-block" :alt="'Slide ' + (index + 1)"
                        style="width: 90px; height: auto;">
                    </button>
                  </div>
                </div>

              </div>
              <div class="col-md-6 d-flex align-items-center">
                <p class="lead teks"> Toko Buku Terlengkap dan Terjangkau di kawasan Kediri, kami
                  menyediakan buku -buku
                  mulai dari buku pengetahuan, buku cerita, buku resep makanan,
                  komik, majalah, hingga ratusan buku lainnya dengan stok yang banyak, kami berkomitmen memberikan
                  layanan terbaik bagi pelanggan kami. </p>
              </div>
            </div>
          </div>
          <br>
          <div class="col-auto text-center m-4 px-3">
            <h2 class="text-dark mb-4 mt-5">Tujuan</h2>
            <p class="lead teks"> Website ini adalah sebuah platform digital berbasis website yang
              dapat memfasilitasi
              pelanggan dalam melakukan pembelian secara online, sehingga pelanggan tidak perlu datang ke toko dalam
              melihat stok buku serta melakukan
              pembelian.
            </p>
          </div>
          <!-- <div class="col-auto text-center m-4 px-3">
            <h2 class="text-dark mb-4 mt-5">Alamat Kami</h2>
            <div class="row">

              <div class="col-md-6">
                <iframe
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3952.7926225546043!2d112.02894431093304!3d-7.81176419217626!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e785769dee1ffc1%3A0x8841f2f6bd62b46d!2sToko%20Buku%20Oscar!5e0!3m2!1sid!2sid!4v1696152777230!5m2!1sid!2sid"
                  width="600" height="500" style="border:0;" allowfullscreen="" loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
              <div class="col-md-6 d-flex align-items-center">
                <p class="lead" style="font-size: 24px;">Kami berada di Jalan Ahmad Yani No. 85, Kecamatan Ngasem, Kabupaten Kediri, Provinsi
                  Jawa Timur</p>
              </div>
            </div>
          </div> -->
          <section class="py-5 ">
            <div class="row align-items-center">
              <h2 class="text-dark mb-4 pb-6 mt-5 text-center">Komitmen Kami</h2>
              <div class="col-md-8">
                <div class="row justify-content-start">
                  <div class="col-md-6">
                    <div color="info" class="info">
                      <span style="font-size: 3rem;">
                        <span style="color: green;">
                          <i class="fas fa-globe"></i>
                        </span>
                      </span>
                      <h5 class="font-weight-bolder mt-3">Jangkauan</h5>
                      <p class="pe-5">Menerima pengiriman produk Toko Buku di Kediri dan sekitarnya</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div color="info" class="info"><span style="font-size: 3rem;">
                        <span style="color: green;">
                          <i class="fa fa-users"></i>
                        </span>
                      </span>
                      <h5 class="font-weight-bolder mt-3">Layanan Online</h5>
                      <p class="pe-5">Website ini menyediakan pemesanan dan pembelian secara online bagi pelanggan,
                        sehingga pelanggan tidak perlu datang ke toko dalam melihat stock barang</p>
                    </div>
                  </div>
                </div>
                <div class="row justify-content-start mt-4">
                  <div class="col-md-6">
                    <div color="info" class="info">
                      <span style="font-size: 3rem;">
                        <span style="color: green;">
                          <i class="fa fa-hand-peace-o"></i>
                        </span>
                      </span>
                      <h5 class="font-weight-bolder mt-3">Kemudahan</h5>
                      <p class="pe-5">Kemudahan dalam penggunaan website sehingga pelanggan dapat melakukan pembelian
                        dan pembayaran dengan mudah.</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div color="info" class="info">
                      <span style="font-size: 3rem;">
                        <span style="color: green;">
                          <i class="fa fa-lock"></i>
                        </span>
                      </span>
                      <h5 class="font-weight-bolder mt-3">Keamanan</h5>
                      <p class="pe-5">Kami akan mengusahakan data pengguna aman pada kami (semoga wkwk)</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4 ms-auto mt-lg-0 mt-6">
                <div class="card">
                  <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2"><a
                      class="d-block blur-shadow-image"><img
                        src="https://feedback.minecraft.net/hc/user_images/RU2A2xA0BE8LK3zqkYnmdw.jpeg"
                        alt="Get insights on Search" class="img-fluid border-radius-lg"></a></div>
                  <div class="card-body text-center">
                    <h5 class="font-weight-normal"><a href="javascript:;">Buku adalah jendela Dunia!</a></h5>
                    <p class="mb-0">Dengan membaca buku, kita bisa mendapatkan beragam pengetahuan yang
                      belum kita
                      ketahui. Sehingga wawasan kita kian bertambah.</p><button type="button"
                      class="btn btn-sm mb-0 mt-3 bg-gradient-success">Beli buku sekarang</button>
                  </div>
                </div>
              </div>
            </div>
          </section>
          <!-- <section class="py-5 mt-5 bg-gradient-dark mb-4" style="border-radius: 30px;">
            <div class="container">
              <div class="row">
                <div class="col-md-8 text-start mb-5 mt-5">
                  <h2 class="text-white"> Team Menpro Kiri, kami <span class="text-white" id="typed"></span></h2>
                  <div id="typed-strings" style="display: none;">
                    <h1>Terbaik</h1>
                    <h1>Cepat</h1>
                    <h1>Kuality</h1>
                  </div>
                  <p class="text-white opacity-8 mb-0"> Persetan dengan hasil bagus, tugas kelar adalah objektif kami.
                  </p>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6 col-12">
                  <div class="card card-profile mt-4">
                    <div class="row">
                      <div class="col-lg-4 col-md-6 col-12 mt-n5"><a href="javascript:;">
                          <div class="p-3 pe-md-0"><img class="w-100 border-radius-md shadow-lg"
                              :src="require('@/assets/img/analyst-1.png')" alt="Emma Roberts"></div>
                        </a></div>
                      <div class="col-lg-8 col-md-6 col-12 my-auto">
                        <div class="card-body ps-lg-0">
                          <h5 class="mb-0">Sembiring 'Enzo' Laridho</h5>
                          <h6 class="text-success">System Analyst</h6>
                          <p class="mb-0">YaDikerjain aja</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 col-12">
                  <div class="card card-profile mt-lg-4 mt-5">
                    <div class="row">
                      <div class="col-lg-4 col-md-6 col-12 mt-n5"><a href="javascript:;">
                          <div class="p-3 pe-md-0"><img class="w-100 border-radius-md shadow-lg"
                              :src="require('@/assets/img/ui-1.png')" alt="William Pearce"></div>
                        </a></div>
                      <div class="col-lg-8 col-md-6 col-12 my-auto">
                        <div class="card-body ps-lg-0">
                          <h5 class="mb-0">Desyalwa 'Najah' Yuga</h5>
                          <h6 class="text-success">UI Designer</h6>
                          <p class="mb-0">Atek quotes barang?</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-lg-6 col-12">
                  <div class="card card-profile mt-4 z-index-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-6 col-12 mt-n5"><a href="javascript:;">
                          <div class="p-3 pe-md-0"><img class="w-100 border-radius-md shadow-lg"
                              :src="require('@/assets/img/fe-1.png')" alt="Ivana Flow"></div>
                        </a></div>
                      <div class="col-lg-8 col-md-6 col-12 my-auto">
                        <div class="card-body ps-lg-0">
                          <h5 class="mb-0">Darmawan 'Farrel' Ahmad</h5>
                          <h6 class="text-success">Frontend Developer</h6>
                          <p class="mb-0">Comot sana comot sini yg penting kelar.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 col-12">
                  <div class="card card-profile mt-lg-4 mt-5 z-index-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-6 col-12 mt-n5"><a href="javascript:;">
                          <div class="p-3 pe-md-0"><img class="w-100 border-radius-md shadow-lg"
                              :src="require('@/assets/img/be-1.png')" alt="Marquez Garcia"></div>
                        </a></div>
                      <div class="col-lg-8 col-md-6 col-12 my-auto">
                        <div class="card-body ps-lg-0">
                          <h5 class="mb-0">Abdullah 'Yasfa' Ainun</h5>
                          <h6 class="text-success">Backend Developer</h6>
                          <p class="mb-0">Alhamdulillah</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section> -->
        </div>
      </div>
    </div>
  </main>

  <app-footer class="py-3 bg-white border-radius-lg" />
</template>

<style scoped>
.thumbnail {
  padding: 5px;
}

.first-two {
  margin-right: 10px;
}

.carousel-indicators button.thumbnail {
  width: 100px;
}

.carousel-indicators button.thumbnail:not(.active) {
  opacity: 0.7;
}

.carousel-indicators {
  position: static;
  margin-right: 50%;
  margin-bottom: 100px;
}


.details-snippet1 {
  color: #585656;
}


.details-snippet1 .mini-preview img {
  border: 1px solid #585656;
  border: 1px solid purple;
  margin-bottom: 100px;
}


@media screen and (min-width: 992px) {
  .carousel {
    max-width: 70%;
    margin: 0 auto;
    margin-right: 5%;
  }

}

.teks {
  font-size: 24px;
}

@media screen and (max-width: 840px) {
  .carousel {
    max-width: 80%;
    margin: 0 auto;
    margin-right: 5%;
  }

  .carousel-indicators {
    position: static;
    margin-right: 10%;
    margin-bottom: 100px;
  }

  .teks {
    font-size: 16px;
  }

}
</style>
