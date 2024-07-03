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
      buku: {
        judul: '',
        desc: '',
        harga: '',
        stok: '',
        foto: '',
        categoriesName: [],
      },
      categories: [],
      selectedTags: [],
      showRow: false
    };
  },
  mounted() {
    this.retrieveCat(),
      this.retrieveBuku()
  },
  computed: {
    formattedHarga: {
      get() {
        return this.buku.harga.toLocaleString('id-ID');
      },
      set(value) {
        const numericValue = parseInt(value.replace(/\./g, ''), 10);
        this.buku.harga = isNaN(numericValue) ? '' : numericValue;
      }
    }
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
        this.categories = response.data;
      } catch (error) {
        console.error(error);
      } finally {
        this.overlay = false;
        this.showRow = true
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

        this.buku = response.data;
        this.breadcrumbsItems[1].title = this.buku.judul;

        this.selectedTags = this.categories
          .filter(cat => this.buku.category.includes(cat.nama))
          .map(cat => cat.nama);

      } catch (error) {
        console.error(error);
      } finally {
        this.overlay = false;
      }
    },
    async saveBuku() {
      const formData = new FormData();
      formData.append('no_isbn', this.buku.no_isbn);
      formData.append('judul', this.buku.judul);
      formData.append('desc', this.buku.desc);
      formData.append('pengarang', this.buku.pengarang);
      formData.append('penerbit', this.buku.penerbit);
      formData.append('tahun_terbit', this.buku.tahun_terbit);
      formData.append('harga', this.buku.harga);
      formData.append('stok', this.buku.stok);
      formData.append('categories', JSON.stringify(this.selectedTags)); 

      if (this.$refs.fileInput.files.length > 0) {
        formData.append('foto', this.$refs.fileInput.files[0]); 
      }

      try {
        const response = await axios.post(`${BASE_URL}/buku/update/${this.buku.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        console.log('Update successful:', response.data);
        this.retrieveBuku();
        this.$refs.fileInput.value = null; 
      } catch (error) {
        console.error('Error updating buku:', error);
      }
    }
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
      <div class="card mb-4 pt-4 border-0" v-if="showRow">
        <div class="row">
          <form @submit.prevent="saveBuku">
            <div class="row d-flex justify-content-center align-items-center">
              <div class="col-12 d-flex justify-content-center align-items-center" style="width: 400px">
                <img :src="buku.foto" alt="Buku" style="max-width: 200px;">
              </div>
            </div>
            <label for="judul">ISBN</label>
            <input class="form-control" type="text" v-model="buku.no_isbn" id="judul" />
            <br>
            <label for="judul">Judul Buku</label>
            <input class="form-control" type="text" v-model="buku.judul" id="judul" />
            <br>
            <label for="desc">Deskripsi</label>
            <textarea class="form-control" v-model="buku.desc" style="height: 150px;" id="desc"></textarea>
            <br>
            <label for="judul">Pengarang</label>
            <input class="form-control" type="text" v-model="buku.pengarang" id="judul" />
            <br>
            <label for="judul">Penerbit</label>
            <input class="form-control" type="text" v-model="buku.penerbit" id="judul" />
            <br>
            <div class="mb-3">
              <label class="form-label">Tags</label>
              <div class="form-check" v-for="tag in categories" :key="tag.id">
                <input class="form-check-input" type="checkbox" :value="tag.nama" v-model="selectedTags">
                <label class="form-check-label">{{ tag.nama }}</label>
              </div>
            </div>
            <label for="file">Upload Foto Baru</label>
            <br>
            <input type="file" ref="fileInput" @change="handleFileChange" id="file" multiple />
            <br>
            <label for="judul" class="mt-2">Tahun Terbit</label>
            <input class="form-control" type="date" v-model="buku.tahun_terbit" id="judul" />
            <br>
            <label for="harga" class="mt-1">Harga</label>
            <div class="input-group">
              <span class="input-group-text">Rp.</span>
              <input type="text" class="form-control" v-model="formattedHarga" id="harga">
            </div>
            <br>
            <label for="stok">Stok Buku</label>
            <input class="form-control" type="text" v-model="buku.stok" id="stok" />
            <br>
            <button class="btn mb-2 btn-primary text-end" type="submit" @click="saveBuku">Save</button>
          </form>
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