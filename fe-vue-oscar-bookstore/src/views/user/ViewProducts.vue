<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
import Breadcrumbs from '@/components/Vuetify/Breadcrumbs.vue';

export default {
  components: {
    Navbar,
    Breadcrumbs
  },
  data() {
    return {
      breadcrumbsItems: [
        {
          title: 'Home',
          disabled: false,
          href: '/home',
        },
        {
          title: '',
          disabled: true,
          href: '/',
        }
      ],
      productsName: '',
      products: [],
      overlay: false,
    };
  },
  mounted() {
    this.getDetailProducts();
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
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    async addToCart(id) {
      try {
        const response = await axios.post(`${BASE_URL}/cart/add`, {
          buku_id: id,
        }, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
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
        }
      }
    },
    async getDetailProducts() {
      try {
        this.overlay = true;

        const slug = this.$route.params.slug;
        const response = await axios.get(`${BASE_URL}/buku/detail/` + slug, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.products = response.data;
        // this.productsName = response.data.judul
        this.products.images = response.data.images;
        this.breadcrumbsItems[1].title = this.products.judul;

        setTimeout(() => {
          this.dialog1 = false;
          this.loading = false;
        }, 500);
      } catch (error) {
        console.error(error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          // Display notification with red color
          this.$notify({
            type: 'error',
            title: 'Error',
            text: errorMessage,
            color: 'red'
          });
        }
      } finally {
        this.overlay = false;
      }
    },
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <div class="py-4 container-fluid">
    <v-overlay v-if="overlay" :model-value="overlay" class="d-flex align-items-center justify-content-center">
      <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
    </v-overlay>
    <div class="container" v-else>
      <Breadcrumbs class="d-flex align-items-center mt-2 text-black" :items="breadcrumbsItems" />
      <div class="row mt-3 px-2">
        <div class="card border-0 " v-if="!loading">
          <div class="row p-2 pt-4">
            <div class="col-md-3 ">
              <img :src="products.foto" class="rounded img-fluid" alt="Book Image">
            </div>
            <div class="col-md-9 d-flex flex-column justify-content-between">
              <a class="title-author mt-1">{{ products.pengarang }}</a>
              <h2 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">{{ products.judul }}</h2>
              <h4> Rp. {{ formatPrice(products.harga) }} </h4>
              <hr>
              <h3 class="title-deskripsi">Deskripsi Buku</h3>
              <div>
                <a style="color: black;"> {{ products.desc }} </a>
              </div>
              <br>
              <div class="mt-auto pb-2">
                <h3 class="title-deskripsi">Detail</h3>
                <div class="row">
                  <div class="row">
                    <div class="col-md-4 col">
                      <span>ISBN <p>{{ products.no_isbn }}</p></span>
                    </div>
                    <div class="col-md-4 col">
                      <span>Penerbit <p>{{ products.penerbit }}</p></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 col">
                      <span>Tanggal Terbit <p>{{ products.tahun_terbit }}</p></span>
                    </div>
                    <div class="col-md-4 col">
                      <span>Kategori <p>{{ products.category }}</p></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 col">
                      <span>Stok <p>{{ products.stok }}</p></span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <h2>Rp. {{ formatPrice(products.harga) }}</h2>
                  </div>
                  <div class="col-md-3">
                    <button class="btn addBtn btn-block mt-2 btn-primary" @click="addToCart(products.id)">Add to
                      cart</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: black;
}

.search-container {
  width: 400px;
  display: block;
  margin: 0 auto;
  padding-right: 30px;
}

.card {
  border-radius: 10px !important;
}

.title-author {
  font-size: 16px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.title-deskripsi {
  font-size: 16px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>