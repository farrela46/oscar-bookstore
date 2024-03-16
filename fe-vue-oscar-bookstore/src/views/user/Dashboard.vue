<script>
// import { useRoute } from "vue-router";
// import axios from "axios";
// import BASE_URL from '@/api/config-api';
// import AuthorsTable from "@/views/components/AuthorsTable.vue";

export default {
  components: {
  },
  data() {
    return {
      products: [],
      searchResults: [
        {
          id: 1,
          images: [{ link: 'https://feedback.minecraft.net/hc/user_images/RU2A2xA0BE8LK3zqkYnmdw.jpeg' }],
          category: { name: 'Category' }, // You may need to adjust this based on your actual data structure
          name: 'Product Name',
          stock: 10,
          price: 100000, // Sample price
          slug: 'product-slug-1' // Sample slug
        },
        {
          id: 2,
          images: [{ link: 'https://feedback.minecraft.net/hc/user_images/RU2A2xA0BE8LK3zqkYnmdw.jpeg' }],
          category: { name: 'Category' }, // You may need to adjust this based on your actual data structure
          name: 'Product Name',
          stock: 10,
          price: 100000, // Sample price
          slug: 'product-slug-1' // Sample slug
        },
        {
          id: 1,
          images: [{ link: 'https://feedback.minecraft.net/hc/user_images/RU2A2xA0BE8LK3zqkYnmdw.jpeg' }],
          category: { name: 'Category' }, // You may need to adjust this based on your actual data structure
          name: 'Product Name',
          stock: 10,
          price: 100000, // Sample price
          slug: 'product-slug-1' // Sample slug
        },

      ]
    };
  },
  mounted() {
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
  },
};
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <router-link :to="'/product/' + item.slug" class="col-md-2 mb-2 col-6" v-for="item in searchResults"
        :key="item.id">
        <div class="product-single-card shadow">
          <div class="product-top-area">
            <div class="product-img">
              <div class="first-view">
                <img :src="item.images[0].link" alt="logo" class="img-fluid">
              </div>
              <div class="hover-view">
                <img :src="item.images[0].link" alt="logo" class="img-fluid">
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
          <div class="product-info">
            <h6 class="product-category"><a href="#">{{ item.category.name }}</a></h6>
            <h6 class="product-title text-truncate"><a href="#">{{ item.name }}</a></h6>
            <div class="d-flex align-items-center">
              <span class="review-count"><b>Stock: </b>{{ item.stock }}</span>
            </div>
            <div class="d-flex flex-wrap align-items-center py-2">
              <div class="new-price">
                Rp. {{ formatPrice(item.price) }}
              </div>
            </div>
          </div>
        </div>
      </router-link>
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
  padding: 20px;
  border-radius: 20px;
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
  aspect-ratio: 1/1;
  overflow: hidden;
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
  font-size: 16px;
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
</style>