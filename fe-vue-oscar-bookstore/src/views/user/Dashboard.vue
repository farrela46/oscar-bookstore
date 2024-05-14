<script>
// import { useRoute } from "vue-router";
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";

// import AuthorsTable from "@/views/components/AuthorsTable.vue";

export default {
  components: {
    Navbar
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
  };
},
mounted() {
  this.retrieveBuku();
},
methods: {
  formatPrice(price) {
    const numericPrice = parseFloat(price);
    return numericPrice.toLocaleString('id-ID');
  },
    async retrieveBuku() {
    try {
      this.overlay = true;
      const response = await axios.get(`${BASE_URL}/buku/get`, {
        headers: {
          Authorization: "Bearer " + localStorage.getItem('access_token')
        }
      });
      this.products = response.data;

      if (response.data.length > 0) {
        this.fotoUrl = response.data[0].foto;
      }
    } catch (error) {
      console.error(error);
    } finally {
      this.overlay = false
    }
  },
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
          <div class="input-group"><span class="input-group-text text-body"><i class="fas fa-search"
                aria-hidden="true"></i></span><input type="text" class="form-control" placeholder="Search Product"
              @input="searchProduct" v-model="searchQuery"></div>
        </form>
      </div>
      <div id="carouselExampleIndicators" class="carousel slide mb-3">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
            aria-current="true" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
            aria-label="Slide 2"></button>
        </div>
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="../../assets/img/promote1.png" class="d-block w-100" alt="...">
          </div>
          <div class="carousel-item">
            <img src="../../assets/img/promote2.jpg" class="d-block w-100" alt="...">
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
              <button class="sideicons-btn">
                <v-icon icon="mdi-heart"></v-icon>
              </button>
              <button class="sideicons-btn" @click.prevent="addCart(item.id)">
                <v-icon icon="mdi-cart-plus"></v-icon>
              </button>
            </div>
          </div>
          <div class="product-info p-2">
            <h6 class="text-muted" style="font-size: 10px"><a href="#">{{ item.pengarang }}</a></h6>
            <h6 class="text-uppercase" style="font-size: 16px;"><a>{{ item.judul }}</a></h6>
            <div class="d-flex align-items-center">
              <a class="text-muted"><b>Stock: </b>{{ item.stok }}</a>
            </div>
            <div class="d-flex align-items-center py-2">
              <a class="text-bold" style="color: blue; font-size: 18px">Rp. {{ formatPrice(item.harga) }}</a>
            </div>
          </div>
        </div>
      </router-link>
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
</style>