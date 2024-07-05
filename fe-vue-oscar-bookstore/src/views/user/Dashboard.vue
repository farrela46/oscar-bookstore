<script>
// import { useRoute } from "vue-router";
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import ArgonInput from "@/components/ArgonInput.vue";


export default {
  components: {
    Navbar,
    ArgonButton,
    ArgonInput
  },
  data() {
    return {
      products: [],
      overlay: false,
      items: [
        {
          title: 'Dashboard',
          disabled: false,
          href: 'breadcrumbs_dashboard',
        },
        {
          title: 'Link 1',
          disabled: false,
          href: 'breadcrumbs_link_1',
        },
        {
          title: 'Link 2',
          disabled: true,
          href: 'breadcrumbs_link_2',
        },
      ],
      showWhatsapp: false,
      inputChat: '',
      searchQuery: '',
      banners: []
    };
  },
  mounted() {
    this.retrieveBuku();
    this.getBanner();
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  methods: {
    toggleWhatsapp() {
      this.showWhatsapp = !this.showWhatsapp;
    },
    sendWhatsapp() {
      const baseUrl = 'https://wa.me/6285179684793';
      const encodedText = encodeURIComponent(this.inputChat.trim());
      const url = `${baseUrl}?text=${encodedText}`;
      window.open(url, '_blank');
      this.inputChat = '',
        this.showWhatsapp = false
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    async addCart(id) {
      try {
        const token = localStorage.getItem('access_token');
        if (!token) {
          this.$notify({
            type: 'error',
            title: 'Notif',
            text: 'Anda belum login. Silakan login terlebih dahulu.',
            color: 'red'
          });

          this.$router.push('/login');
          return;
        }
        const response = await axios.post(`${BASE_URL}/cart/add`, {
          buku_id: id,
        }, {
          headers: {
            Authorization: "Bearer " + token
          }
        });

        console.log("Product added to cart:", response.data);

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Product added to cart',
          color: 'green'
        });
      } catch (error) {
        console.error("Error adding product to cart:", error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          this.$notify({
            type: 'error',
            title: 'Error',
            text: errorMessage,
            color: 'red'
          });
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'An error occurred while adding the product to the cart.',
            color: 'red'
          });
        }
      }
    },
    searchProduct() {
      this.retrieveBuku(this.searchQuery);
    },
    async getBanner() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/banner/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.banners = response.data;

        setTimeout(() => {
          this.overlay = false;
        }, 3000);

      } catch (error) {
        console.error(error);
        this.overlay = false;
      }
    },

    async retrieveBuku(searchQuery = '') {
      this.overlay = true;
      try {
        const params = {};
        if (searchQuery) params.search = searchQuery;

        const response = await axios.get(`${BASE_URL}/buku/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          },
          params
        });

        this.products = response.data;
        if (response.data.length > 0) {
          this.fotoUrl = response.data[0].foto;
        }

        setTimeout(() => {
          this.overlay = false;
        }, 3000);

      } catch (error) {
        console.error(error);
        this.overlay = false;
      }
    },


    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = false;
      this.store.state.showFooter = false;
      this.body.classList.remove("bg-gray-100");
    },
    restorePage() {
      this.store.state.hideConfigButton = false;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
      this.store.state.showFooter = true;
      this.body.classList.add("bg-gray-100");
    }
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />

  <div class="py-4 container">
    <div class="row mt-2">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
      <div class="row" style="height: 60px;">
        <form class="search-container" @submit.prevent="searchProduct" style="max-width: 350px;">
          <div class="input-group">
            <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
            <input type="text" class="form-control" placeholder="Search Product" @input="searchProduct"
              v-model="searchQuery">
          </div>
        </form>
      </div>
      <div id="carouselExampleIndicators" class="carousel slide mb-3">
        <div class="carousel-indicators">
          <button v-for="(banner, index) in banners" :key="index" type="button"
            :data-bs-target="'#carouselExampleIndicators'" :data-bs-slide-to="index" :class="{ 'active': index === 0 }"
            :aria-current="index === 0 ? 'true' : ''" :aria-label="'Slide ' + (index + 1)"></button>
        </div>
        <div class="carousel-inner">
          <div v-for="(banner, index) in banners" :key="index" :class="['carousel-item', { active: index === 0 }]">
            <img :src="banner.foto" class="d-block w-100" :alt="banner.judul" style="width: 100%;
  max-height: 300px;
  object-fit: cover;">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
          data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
          data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>

      <router-link :to="'/products/' + item.slug" class="col-md-2 mb-2 col-6" v-for="item in products" :key="item.id">
        <div class="product-single-card shadow">
          <div class="product-top-area">
            <div class="product-img">
              <div class="first-view">
                <img :src="item.foto" alt="Book Image" class="img-fluid">
              </div>
              <div class="hover-view">
                <img :src="item.foto" alt="Book Image" class="img-fluid">
              </div>
            </div>
            <div class="sideicons">
              <button class="sideicons-btn" @click.prevent="addCart(item.id)">
                <v-icon icon="mdi-cart-plus"></v-icon>
              </button>
            </div>
          </div>
          <div class="product-info p-2">
            <h6 class="text-muted" style="font-size: 10px"><a href="#">{{ item.pengarang }}</a></h6>
            <h6 class="text-uppercase  text-truncate" style="font-size: 16px;"><a>{{ item.judul }}</a></h6>
            <div class="d-flex align-items-center">
              <a class="text-muted"><i class="fas fa-star mx-1" style="color: #FFEB3B;"></i>{{ item.average_rating }}
                &#x2022; {{ item.sold }} Terjual</a>
            </div>
            <div class="d-flex align-items-center">
              <a class="text-bold" style="color: blue; font-size: 18px">Rp. {{ formatPrice(item.harga) }}</a>
            </div>
          </div>
          <!-- <div class="px-2 pb-1">
            <argon-button>Buy</argon-button>
          </div> -->
        </div>
      </router-link>
    </div>
    <div class="fixed-plugin">
      <a class="p-3 py-2 fixed-plugin-button text-dark position-fixed" @click="toggleWhatsapp">
        <i class=" py-2 fab fa-whatsapp"></i>
      </a>
    </div>
    <div v-if="showWhatsapp" class="whatsapp-chat-window position-fixed">
      <div class="whatsapp-header">
        <i class="fab fa-whatsapp"></i> WhatsApp
        <button @click="toggleWhatsapp" class="close-button">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="whatsapp-body">
        <p>Hai, Kak...</p>
        <p>Ada yang bisa kami bantu?</p>
      </div>
      <div class="row ">
        <div class="col-7">
          <argon-input v-model="inputChat" class="ms-1" id="Text" type="text" placeholder="Tanya disini" name="email"
            size="md" />
        </div>
        <div class="col-5">
          <argon-button color="success" size="sm" variant="contained" @click="sendWhatsapp">Kirim</argon-button>
        </div>
      </div>
    </div>
  </div>

</template>
<style>
.bg-langit {
  background-color: #42BADB;
  background-size: 30vh;
}

.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: unset;
}


.product-single-card {
  /* padding: 10px; */
  border-radius: 5px;
  box-shadow: 1px 1px 15px #cccccc40;
  transition: 0.5s ease-in;
  background-color: white;
}

.product-single-card:hover {
  -webkit-box-shadow: 1px 1px 28.5px -7px #d6d6d6;
  -moz-box-shadow: 1px 1px 28.5px -7px #d6d6d6;
  box-shadow: 1px 1px 28.5px -7px #d6d6d6;
}

.product-single-card .product-info {
  padding: 15px 0 0 0;
}

.product-single-card .product-top-area {
  position: relative;
  display: flex;
  align-items: center;
  overflow: hidden;
  border-radius: 5px;
}

.product-single-card .product-top-area .product-discount {
  position: absolute;
  top: 10px;
  left: 10px;
  background: white;
  border-radius: 3px;
  padding: 5px 10px;
  box-shadow: 1px 1px 28.5px -7px #dddddd;
  user-select: none;
  z-index: 999;
}

.product-single-card .product-top-area .product-img {
  aspect-ratio: 3/4;
  overflow: hidden;
  object-fit: fill;
}

.product-single-card .product-top-area .product-img .first-view {
  transition: 0.5s ease-in;
}

.product-single-card .product-top-area .product-img .hover-view {
  opacity: 0;
  transition: 0.5s ease-in;
}

.product-single-card .product-top-area .product-img img {
  /* width: 250px;  */
  /* height: 150px;  */
  /* object-fit: cover; */
  /* Add a 5px solid white border */
  box-sizing: border-box;
}

.product-single-card .product-top-area .sideicons {
  position: absolute;
  right: 15px;
  display: grid;
  gap: 10px;
}

.product-single-card .product-top-area .sideicons .sideicons-btn {
  background-color: #fff;
  color: #000;
  border-radius: 50%;
  border: none;
  width: 35px;
  height: 35px;
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0;
  visibility: hidden;
  transform: translateX(60px);
  transition: 0.3s ease-in;
  -webkit-box-shadow: 1px 1px 28.5px -7px #dddddd;
  -moz-box-shadow: 1px 1px 28.5px -7px #dddddd;
  box-shadow: 1px 1px 28.5px -7px #dddddd;
}

.product-single-card .product-top-area .sideicons .sideicons-btn:hover {
  color: #fff;
  background-color: #000;
}

.product-single-card .product-top-area .sideicons .sideicons-btn:nth-child(1) {
  transition-delay: 100ms;
}

.product-single-card .product-top-area .sideicons .sideicons-btn:nth-child(2) {
  transition-delay: 200ms;
}

.product-single-card .product-top-area .sideicons .sideicons-btn:nth-child(3) {
  transition-delay: 300ms;
}

.product-single-card .product-top-area .sideicons .sideicons-btn:nth-child(4) {
  transition-delay: 400ms;
}

.product-single-card .product-top-area:hover .sideicons .sideicons-btn {
  opacity: 100%;
  visibility: visible;
  transform: translateX(0);
}

.product-single-card .product-info .product-category {
  font-weight: 600;
  opacity: 60%;
}

.product-single-card .product-info .product-title {
  font-size: 18px;
  font-weight: 600;
}

.product-single-card .product-info .old-price,
.product-single-card .product-info .new-price {
  padding-right: 15px;
  font-size: 18px;
  font-weight: 600;
  letter-spacing: 1px;
}

.product-single-card .product-info .old-price {
  text-decoration: line-through;
  opacity: 70%;
}


.search-container {
  width: 400px;
  display: block;
  margin: 0 auto;
  padding-right: 30px;
}

input#search-bar {
  margin: 0 auto;
  width: 100%;
  height: 45px;
  padding: 0 20px;
  font-size: 1rem;
  background-color: white;
  border: 1px solid #D0CFCE;
  outline: none;

  &:focus {
    border: 1px solid #D0011B;
    transition: 0.35s ease;
    color: #D0011B;

    &::-webkit-input-placeholder {
      transition: opacity 0.45s ease;
      opacity: 0;
    }

    &::-moz-placeholder {
      transition: opacity 0.45s ease;
      opacity: 0;
    }

    &:-ms-placeholder {
      transition: opacity 0.45s ease;
      opacity: 0;
    }
  }
}

.search-icon {
  position: relative;
  float: right;
  width: 75px;
  height: 75px;
  top: -62px;
  right: -70px;
}

.search-container {
  width: 400px;
  display: block;
  margin: 0 auto;
  padding-right: 30px;
}


.whatsapp-chat-window {
  width: 300px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  background-color: white;
  bottom: 90px;
  right: 30px;
}

.whatsapp-header {
  background-color: #25d366;
  color: white;
  padding: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.whatsapp-body {
  padding: 10px;
  font-size: 14px;
}



.open-chat-button {
  background-color: #25d366;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.close-button {
  background: none;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
}
</style>