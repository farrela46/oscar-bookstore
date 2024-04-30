<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';

export default {
  components: {
  },
  data() {
    return {
      products: [],
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
    <div class="container">
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

.product-single-card {
  padding: 10px;
  border-radius: 10px;
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

/* 
.product-single-card .product-top-area:hover .product-img .first-view {
  opacity: 0;
  width: 0;
  height: 0;
}

.product-single-card .product-top-area:hover .product-img .hover-view {
  opacity: 100%;
  scale: 1.2;
} */
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

.card {
  border-radius: 10px !important;
}
</style>