<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
import Breadcrumbs from '@/components/Vuetify/Breadcrumbs.vue';
import ArgonButton from "@/components/ArgonButton.vue";
import ArgonInput from "@/components/ArgonInput.vue";

export default {
  components: {
    Navbar,
    Breadcrumbs,
    ArgonButton,
    ArgonInput
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
      showWhatsapp: false,
      inputChat: '',
      reviews: [],
      isMobile: false,
    };
  },
  mounted() {
    import('vuetify/styles').then(() => {
      this.getDetailProducts();
    })
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
    this.checkScreenSize();
    window.addEventListener('resize', this.checkScreenSize);
  },
  beforeUnmount() {
    this.restorePage();
  },
  unMounted() {
    window.removeEventListener('resize', this.checkScreenSize);
  },
  methods: {
    formatDate(date) {
      if (!date) return "";
      const options = { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
      return new Date(date).toLocaleDateString('id-ID', options);
    },
    checkScreenSize() {
      this.isMobile = window.innerWidth < 768; // Bootstrap's mobile breakpoint
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
    },
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
    async addToCart(id) {
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
          text: 'Buku ditambahkan ke Keranjang',
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

        const reviewData = await axios.get(`${BASE_URL}/review/${this.products.id}`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.reviews = reviewData.data.data;

        setTimeout(() => {
          this.dialog1 = false;
          this.loading = false;
        }, 500);
      } catch (error) {
        console.error(error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
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

    <!-- Desktop Version -->
    <div v-if="!isMobile && !overlay" class="container">
      <Breadcrumbs class="d-flex align-items-center mt-2 text-dark" :items="breadcrumbsItems" />
      <div class="row mt-3 px-2">
        <div class="card border-0">
          <div class="row p-2 pt-4">
            <div class="col-md-3">
              <img :src="products.foto" class="rounded img-fluid" alt="Book Image">
            </div>
            <div class="col-md-9 d-flex flex-column justify-content-between">
              <a class="title-author mt-1 text-dark">{{ products.pengarang }}</a>
              <h2 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">{{ products.judul }}</h2>
              <v-rating v-model="products.average_rating" density="compact" readonly half-increments hover active-color="yellow"
              color="grey"></v-rating>
              <hr>
              <h3 class="title-deskripsi">Deskripsi Buku</h3>
              <div>
                <a style="color: black;">{{ products.desc }}</a>
              </div>
              <br>
              <div class="mt-auto pb-2">
                <h3 class="title-deskripsi">Detail</h3>
                <div class="row">
                  <div class="row">
                    <div class="col-md-4 col">
                      <span><strong>ISBN</strong>
                        <p>{{ products.no_isbn }}</p>
                      </span>
                    </div>
                    <div class="col-md-4 col">
                      <span><strong>Penerbit</strong>
                        <p>{{ products.penerbit }}</p>
                      </span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 col">
                      <span><strong>Tanggal Terbit</strong>
                        <p>{{ products.tahun_terbit }}</p>
                      </span>
                    </div>
                    <div class="col-md-4 col">
                      <span><strong>Kategori</strong>
                        <p>{{ products.category }}</p>
                      </span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 col">
                      <span><strong>Stok</strong>
                        <p>{{ products.stok }}</p>
                      </span>
                    </div>
                    <div class="col-md-4 col">
                      <span><strong>Terjual</strong>
                        <p>{{ products.sold }}</p>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <h2>Rp. {{ formatPrice(products.harga) }}</h2>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-sm btn-block mt-2 btn-primary" @click="addToCart(products.id)"> <i
                        class="fas fa-shopping-cart"></i> Beli</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <h4 class="mb-0">Reviews</h4>
              </div>
            </div>
            <div class="card-body">
              <div v-if="reviews.length > 0">
                <div v-for="item in reviews" :key="item.id" style="color: black">
                  <div class="row mt-2">
                    <div class="col-12">
                      <div class="px-4">
                        <div class="row">
                          <v-rating class="mt-2" density="compact" readonly v-model="item.rating" active-color="yellow"
                            color="grey"></v-rating>
                          <div class="d-flex align-items-center">
                            <div class="mt-2">
                              <a class="text-black"><strong> {{ item.user.name }}</strong></a>
                              <a class="ms-3 text-black" style="font-size: 12px;">{{ formatDate(item.created_at) }}</a>
                            </div>
                          </div>
                          <div class="row mt-2">
                            <p class="text-black" style="font-size: 14px;">{{ item.comment }}</p>
                          </div>
                        </div>
                        <hr>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="col-12 text-center"
                style="border-radius: 10px; padding: 20px; background-color: #f8f9fa;">
                <p class="text-dark" style="margin: 0; font-size: 18px;">Belum ada Review</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Version -->
    <div v-else-if="isMobile && !overlay" class="d-block d-md-none">
      <Breadcrumbs class="d-flex align-items-center mt-2 text-black" :items="breadcrumbsItems" />
      <div class="row mt-3 px-2">
        <div class="card border-0">
          <div class="row p-2 pt-4">
            <div class="col-12 d-flex justify-content-center align-items-center">
              <img :src="products.foto" class="rounded book-image-mobile" alt="Book Image">
            </div>
            <div class="col-12 d-flex flex-column justify-content-between" style="font-size: 12px">
              <a class="title-author mt-1 text-dark">{{ products.pengarang }}</a>
              <h4 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">{{ products.judul }}</h4>
              <hr>
              <h5 class="title-deskripsi">Deskripsi Buku</h5>
              <div>
                <a style="color: black;"> {{ products.desc }} </a>
              </div>
              <br>
              <div class="mt-auto pb-2">
                <h3 class="title-deskripsi">Detail</h3>
                <div class="row">
                  <div class="row">
                    <div class="col-md-4 col">
                      <span><strong>ISBN</strong>
                        <p>{{ products.no_isbn }}</p>
                      </span>
                    </div>
                    <div class="col-md-4 col">
                      <span><strong>Penerbit</strong>
                        <p>{{ products.penerbit }}</p>
                      </span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 col">
                      <span><strong>Tanggal Terbit</strong>
                        <p>{{ products.tahun_terbit }}</p>
                      </span>
                    </div>
                    <div class="col-md-4 col">
                      <span><strong>Kategori</strong>
                        <p>{{ products.category }}</p>
                      </span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 col">
                      <span><strong>Stok</strong>
                        <p>{{ products.stok }}</p>
                      </span>
                    </div>
                    <div class="col-md-4 col">
                      <span><strong>Terjual</strong>
                        <p>{{ products.sold }}</p>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <h2>Rp. {{ formatPrice(products.harga) }}</h2>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-sm btn-block mt-2 btn-primary" @click="addToCart(products.id)"><i
                        class="fas fa-shopping-cart"></i> Beli</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <h4 class="mb-0">Reviews</h4>
              </div>
            </div>
            <div class="card-body">
              <div v-if="reviews.length > 0">
                <div v-for="item in reviews" :key="item.id" style="color: black">
                  <div class="row mt-2">
                    <div class="col-12">
                      <div class="px-4">
                        <div class="row">
                          <v-rating class="mt-2" density="compact" readonly v-model="item.rating" active-color="yellow"
                            color="grey"></v-rating>
                          <div class="d-flex align-items-center">
                            <div class="mt-2">
                              <a class="text-black" style="font-size: 18px"><strong> {{ item.user.name }}</strong></a>
                              <a class="ms-3 text-black" style="font-size: 12px;">{{ formatDate(item.created_at) }}</a>
                            </div>
                          </div>
                          <div class="row mt-2">
                            <a class="text-black" style="font-size: 12px;">{{ item.comment }}</a>
                          </div>
                        </div>
                        <hr>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="col-12 text-center"
                style="border-radius: 10px; padding: 20px; background-color: #f8f9fa;">
                <p class="text-dark" style="margin: 0; font-size: 18px;">Belum ada Review</p>
              </div>
            </div>
          </div>
        </div>
      </div>
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

.book-image-mobile {
  max-width: 150px;
  margin: 0 auto;
  /* Center the image */
}
</style>