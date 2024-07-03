<script>
// import { useRoute } from "vue-router";
import axios from "axios";
import BASE_URL from '@/api/config-api';
// import AuthorsTable from "@/views/components/AuthorsTable.vue";
import Navbar from "@/examples/Navbars/Navbar.vue";
import Breadcrumbs from '@/components/Vuetify/Breadcrumbs.vue';

export default {
  components: {
    Navbar,
    Breadcrumbs,
  },
  data() {
    return {
      breadcrumbsItems: [
        {
          title: 'Product',
          disabled: false,
          href: '/admin/products',
        },
        {
          title: '',
          disabled: true,
          href: '/',
        }
      ],
      overlay: false,
      currentCategory: '',
      currentCategoryID: '',
      categories: [],
      buku: {
        judul: '',
        no_isbn: '',
        desc: '',
        pengarang: '',
        penerbit: '',
        tahun_terbit: '',
        harga: '',
        stok: '',
        foto: null,
        categoriesName: []
      },
    };
  },
  mounted() {
    this.retrieveCat(),
      this.retrieveBuku()
  },
  methods: {
    async retrieveCat() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/category/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.categories = response.data

        if (response.data.length > 0) {
          this.fotoUrl = response.data[0].foto;
        }
      } catch (error) {
        console.error(error);
      } finally {
        this.overlay = false;
      }
    },
    
    async retrieveBuku() {
      this.overlay = true;
      try {

        const slug = this.$route.params.slug;
        const response = await axios.get(`${BASE_URL}/buku/detail/` + slug, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.buku = response.data
        this.breadcrumbsItems[1].title = this.buku.judul;

        if (response.data.length > 0) {
          this.fotoUrl = response.data[0].foto;
        }
      } catch (error) {
        console.error(error);
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
    <Breadcrumbs class="d-flex align-items-center mt-2 text-dark" :items="breadcrumbsItems" />
    <div class="row mt-3">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
      <div class="row">
        <div class="col-md-12">
          <div class="card mb-4 pt-4 border-0 px-2">
            <div class="col px-4">
              <form @submit.prevent="saveParfum">
                <div class="row d-flex justify-content-center align-items-center">
                  <div class="col-12 " style="width: 400px">
                    <v-carousel show-arrows="hover" height="auto">
                      <v-carousel-item :src="buku.foto" cover></v-carousel-item>
                    </v-carousel>
                  </div>
                </div>
                <div class="row">
                </div>
                <label for="judul">Nama Parfum</label>
                <input class="form-control" type="text" v-model="buku.judul" id="judul" />
                <br>
                <label for="desc">Deskripsi</label>
                <textarea class="form-control" v-model="buku.desc" id="desc"></textarea>
                <br>
                <div class="mb-3">
                  <label class="form-label">Tags</label>
                  <div class="form-check" v-for="tag in tags" :key="tag.id">
                    <input class="form-check-input" type="checkbox" :value="tag.name" v-model="selectedTags">
                    <label class="form-check-label">{{ tag.name }}</label>
                  </div>
                </div>
                <br>
                <label for="file">Upload Foto Baru</label>
                <br>
                <input type="file" ref="fileInput" @change="handleFileChange" id="file" multiple />
                <br>
                <label for="harga" class="mt-3">Harga</label>
                <div class="input-group">
                  <span class="input-group-text">Rp.</span>
                  <input type="text" class="form-control" v-model="buku.harga" id="harga">
                </div>
                <br>
                <label for="stok">Stok parfum</label>
                <input class="form-control" type="text" v-model="buku.harga" id="stok" />
                <br>
                <button-custom class="btn btn-info mb-2" type="submit" @click="saveParfum">Save Parfum</button-custom>
              </form>
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
</style>