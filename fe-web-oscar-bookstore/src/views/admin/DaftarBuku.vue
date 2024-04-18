<!-- Catalogue.vue -->
<template>
  <Navbar>
    <div>
      <div class="container-fluid px-4 py-2">
        <div class="row">
          <div class="col-md-12">
            <div class="card border-0 px-2 " style="text-align: end;">
              <div class="col text-right">
                <div class="button-set my-4">
                  <button-custom data-bs-toggle="modal" data-bs-target="#addBuku">Tambah Buku</button-custom>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-12 mt-4">
            <Datatables ref="datatablesBuku" />
          </div>
        </div>
      </div>
      <!-- Modal Nich -->
      <div class="modal fade" id="addBuku" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah DaftarBuku</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeModal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="addBuku">
                <div class="mb-3">
                  <label for="exampleInputPassword" class="form-label">No ISBN</label>
                  <input type="text" class="form-control" v-model="buku.no_isbn">
                </div>
                <div class="mb-3">
                  <label for="exampleInputEmail1" class="form-label">Judul Buku</label>
                  <input type="name" class="form-control" v-model="buku.judul" aria-describedby="namaCustomer">
                </div>
                <div class="mb-3">
                  <label for="exampleInputDesc" class="form-label">Deskripsi</label>
                  <textarea type="text" class="form-control" v-model="buku.desc" aria-describedby="emailCustomer"></textarea>
                </div>
                <div class="mb-3">
                  <label for="exampleInputPengarang" class="form-label">Pengarang</label>
                  <input type="text" class="form-control" v-model="buku.pengarang">
                </div>
                <div class="mb-3">
                  <label for="exampleInputPenerbit" class="form-label">Penerbit</label>
                  <input type="text" class="form-control" v-model="buku.penerbit">
                </div>
                <div class="mb-3">
                  <label for="exampleInputTahunTerbit" class="form-label">Tahun Terbit</label>
                  <input type="date" class="form-control" v-model="buku.tahun_terbit">
                </div>
                <div class="mb-3">
                  <label for="FileInput" class="form-label">Gambar Buku</label>
                  <input type="file" class="form-control" ref="fileInput" @change="handleFileChange">
                </div>
                <div class="mb-3">
                  <label for="exampleInputHarga" class="form-label">Harga</label>
                  <input type="text" class="form-control" v-model="buku.harga">
                </div>
                <div class="mb-3">
                  <label for="exampleInputStock" class="form-label">Stok</label>
                  <input type="text" class="form-control" v-model="buku.stok">
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button-custom type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button-custom>
              <button-custom class="btn btn-info" type="submit" @click="addBuku">Tambah Buku</button-custom>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Navbar>
</template>
  
<script>
import axios from 'axios';
import Navbar from '@/components/AdminNavbar.vue';
import Datatables from '@/components/Vuetify/DataTablesBuku.vue';

const BASE_URL = import.meta.env.VITE_BASE_URL_API;

export default {
  name: 'DaftarBuku',
  components: {
    Navbar,
    Datatables
  },
  data() {
    return {

      // Modal Add Customer
      buku: {
        judul: '',
        no_isbn: '',
        desc: '',
        pengarang: '',
        penerbit: '',
        tahun_terbit: '',
        harga: '',
        stok: '',
      },
      selectedFile: '',
    }
  },
  methods: {
    // Prompt
    closeModal() {
      document.getElementById('closeModal').click();
    },

    clearForm() {
      this.buku.judul = '';
      this.buku.no_isbn = '';
      this.buku.desc = '';
      this.buku.pengarang = '';
      this.buku.penerbit = '';
      this.buku.tahun_terbit = '';
      this.buku.harga = '';
      this.buku.stok = '';
      this.buku.foto = '';
      this.selectedFile = '';
    },


    // Method
    handleFileChange(event) {
      const fileInput = this.$refs.fileInput;
      this.selectedFile = fileInput.files[0];
    },
    // async addBuku() {
    //   try {
    //     const response = await axios.post(BASE_URL + '/register', {
    //       judul: this.judul,
    //       no_isbn: this.no_isbn,
    //       desc: this.desc,
    //       pengarang: this.pengarang,
    //       penerbit: this.penerbit,
    //       tahun_terbit: this.tahun_terbit,
    //       harga: this.harga,
    //       stok: this.stok,
    //     });
    //     this.closeModal(),
    //       this.$notify({
    //         type: 'success',
    //         title: 'Success',
    //         text: response.data.message,
    //         color: 'green'
    //       });
    //     this.$refs.datatablesComp.retrieveUser();
    //     this.clearForm();
    //     console.log(response.data);
    //   } catch (error) {
    //     console.error(error);

    //     if (error.response && error.response.data.message) {
    //       const errorMessage = error.response.data.message;
    //       // Display notification with red color
    //       this.$notify({
    //         type: 'error',
    //         title: 'Error',
    //         text: errorMessage,
    //         color: 'red'
    //       });
    //     }
    //   }
    // },
    async addBuku() {
      try {
        // Create a FormData object to handle file upload
        const formData = new FormData();

        // Append the selected file
        formData.append('foto', this.selectedFile, this.selectedFile.name);
        formData.append('judul', this.buku.judul);
        formData.append('no_isbn', this.buku.no_isbn);
        formData.append('desc', this.buku.desc);
        formData.append('pengarang', this.buku.pengarang);
        formData.append('penerbit', this.buku.penerbit);
        formData.append('tahun_terbit', this.buku.tahun_terbit);
        formData.append('harga', this.buku.harga);
        formData.append('stok', this.buku.stok);
        const token = localStorage.getItem('access_token')
        const response = await axios.post(BASE_URL + '/buku/add', formData, {
          headers: {
            'Content-Type': 'multipart/form-data', // Set content type for file upload
            'Authorization': 'Bearer ' + token, // Include Bearer token in the Authorization header
          },
        });
        console.log(response.data);

        this.closeModal();
        this.$refs.datatablesBuku.retrieveBuku();
        this.clearForm();

        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green',
        });
      } catch (error) {
        console.error(error);
        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          this.$notify({
            type: 'error',
            title: 'Error',
            text: errorMessage,
            color: 'red',
          });
        }
      }
    },
  }
};
</script>
  
<style scoped>
.dashboard-admin {
  min-height: 100vh;

  background: url("../../../src/assets/LandingPage/Background.png");
  background-position: center;
  background-size: cover;
}

.button-set {
  display: block;
  width: 100%;
  clear: both;
  margin: 15px 0;

}

button-custom,
.button {
  outline: 0 none;
  border: 0 none;
  padding: 13px 30px;
  background-color: #0771B8;
  background-image: linear-gradient(45deg, #0F7AC0 0%, #3CB7CB 50%, #4BC7CF 90%);
  background-position: 100% 0;
  background-size: 200% 200%;
  color: #FFF;
  font-size: 12px;
  text-transform: uppercase;
  border-radius: 20px;
  letter-spacing: 2px;
  transition: .3s;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.5s ease;

  &:hover {
    background-position: 0 0;
  }
}
</style>
  