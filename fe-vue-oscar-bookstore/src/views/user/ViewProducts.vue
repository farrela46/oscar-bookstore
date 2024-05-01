<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';

export default {
  components: {
  },
  data() {
    return {
      products: [],
      overlay: false,
    };
  },
  mounted() {
    this.retrieveBuku();
    this.getDetailProducts();
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
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
        this.products.images = response.data.images;

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
    async retrieveBuku() {
      try {
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
      }
    },
  },
};
</script>

<template>
  <div class="py-4 container-fluid">
    <v-overlay v-if="overlay" :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
    <div class="container" v-else>
      <div class="row mt-3">
        <div class="card border-0" v-if="!loading">
          <div class="row p-2 pt-4">
            <div class="col-md-3 ">
              <img :src="products.foto" class="rounded img-fluid" alt="Book Image">
            </div>
            <div class="col-md-9 d-flex flex-column justify-content-between">
              <a style="font-size: 32px; font-weight: bold;">{{ products.judul }}</a>

              <!-- <div class="ratings">
                  <div class="stars d-flex">
                    <div class="theme-text mr-2">Product Ratings: </div>
                    <div>&#9733;</div>
                    <div>&#9733;</div>
                    <div>&#9733;</div>
                    <div>&#9733;</div>
                    <div>&#9733;</div>
                    <div class="ml-2">(4.5) 50 Reviews</div>
                  </div>
                </div> -->
              <div class="price my-2" style="font-weight: bold; font-size: 32px;">Rp. {{ formatPrice(products.harga) }}
              </div>
              <div class="theme-text subtitle">Deskripsi:</div>
              <div class="brief-description">
                {{ products.desc }}
              </div>
              <br>
              <div class="mt-auto pb-2">
                <!-- <a>Tags:</a>
                <p>Tag</p>
                <a>Category &nbsp;: </a>
                <br> -->

                <h5>Detail</h5>
                <a>No ISBN:</a>
                <p>{{ products.no_isbn }}</p>
                <a>Pengarang:</a>
                <p>{{ products.pengarang }}</p>
                <a>Penerbit:</a>
                <p>{{ products.penerbit }}</p>
                <!-- <v-chip class="mt-3">Tag</v-chip> -->
                <hr>
                <div class="row">
                  <!-- <div class="col-md-3 mt-2">
                      <input type="number" class="form-control" value="1">
                    </div> -->
                  <div class="col-md-9">
                    <button class="btn addBtn btn-block mt-2 btn-primary" @click="addToCart(parfum.id)">Add to
                      basket</button>
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
  color: unset;
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
</style>