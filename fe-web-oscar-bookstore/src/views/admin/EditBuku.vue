<!-- Catalogue.vue -->
<template>
  <Navbar>
    <div class="dashboard-admin">
      <div class="container-fluid px-4 py-2">
        <div class="row">
          <div class="col-md-12">
            <div class="border-0 px-2 mb-4">
              here
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="card mb-4 pt-4 border-0 px-2">
              <div class="col px-4">
                <form @submit.prevent="saveBuku">
                  <div class="mb-3 d-flex justify-content-center">
                    <img :src="foto" alt="Buku Image" style="max-width: 200px;" />
                  </div>
                  <label for="judul">No ISBN</label>
                  <input class="form-control" type="text" v-model="no_isbn" id="no_isbn" />
                  <br>
                  <label for="judul">Judul</label>
                  <input class="form-control" type="text" v-model="judul" id="judul" />
                  <br>
                  <label for="desc">Deskripsi</label>
                  <textarea class="form-control" v-model="desc" id="desc"></textarea>
                  <br>
                  <label for="pengarang">Pengarang</label>
                  <input class="form-control" type="text" v-model="pengarang" id="pengarang" />
                  <br>
                  <label for="penerbit">Penerbit</label>
                  <input class="form-control" type="text" v-model="penerbit" id="penerbit" />
                  <br>
                  <label for="tahun_terbit">Tahun Terbit</label>
                  <input class="form-control" type="text" v-model="tahun_terbit" id="tahun_terbit" />
                  <br>
                  <label for="file">Upload Foto Baru</label>
                  <br>
                  <input type="file" ref="fileInput" @change="handleFileChange" id="file" />
                  <br>
                  <label for="harga" class="mt-3">Harga Buku</label>
                  <div class="input-group">
                    <span class="input-group-text">Rp.</span>
                    <input type="text" class="form-control" v-model="harga" id="harga">
                  </div>
                  <br>
                  <label for="stok">Stok Buku</label>
                  <input class="form-control" type="text" v-model="stok" id="stok" />
                  <br>
                  <button-custom class="btn btn-info mb-2" type="submit" @click="saveBuku">Save Buku</button-custom>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Navbar>
</template>
  
<script>
import Navbar from '@/components/AdminNavbar.vue';
import axios from 'axios';
const BASE_URL = import.meta.env.VITE_BASE_URL_API


export default {
  name: 'EditBuku',
  components: {
    Navbar
  },
  data() {
    return {
      dataLoaded: false,
      id: '',
      judul: '',
      no_isbn: '',
      desc: '',
      pengarang: '',
      penerbit: '',
      tahun_terbit: '',
      harga: '',
      stok: '',
      selectedFile: '',
      fotoFile: null,
    }
  },
  async mounted() {
    try {
      const id_buku = this.$route.params.id;
      const response = await axios.get(BASE_URL + '/buku/detail/' + id_buku, {
        headers: {
          Authorization: "Bearer " + localStorage.getItem('access_token')
        }
      });

      const data = response.data;
      this.judul = data.judul;
      this.no_isbn = data.no_isbn;
      this.desc = data.desc;
      this.pengarang = data.pengarang;
      this.penerbit = data.penerbit;
      this.tahun_terbit = data.tahun_terbit;
      this.harga = data.harga;
      this.stok = data.stok;

      this.foto = response.data.foto;
      console.log(this.foto)
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
  computed: {
    imageUrl() {
      return BASE_URL + '/' + this.foto;
    }
  },
  methods: {
    handleFileChange(event) {
      this.fotoFile = event.target.files[0];
    },
    async saveBuku() {
      try {
        const formData = new FormData();
        formData.append('no_isbn', this.no_isbn);
        formData.append('judul', this.judul);
        formData.append('desc', this.desc);
        formData.append('pengarang', this.pengarang);
        formData.append('penerbit', this.penerbit);
        formData.append('tahun_terbit', this.tahun_terbit);
        formData.append('harga', this.harga);
        formData.append('stok', this.stok);
        formData.append('foto', this.fotoFile); // Include the selected file

        const response = await axios.post(BASE_URL + '/buku/update/' + this.$route.params.id, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        });
        this.$router.go();
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Buku telah ter-update!',
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
            color: 'red'
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

.form-box {
  position: relative;
  width: 400px;
  height: 450px;
  background: transparent;
  border: 2px solid rgba(0, 0, 0, 0.5);
  border-radius: 20px;
  backdrop-filter: blur(15px);
  display: flex;
  justify-content: center;
  align-items: center;

}

h2 {
  font-size: 2em;
  color: black;
  text-align: center;
}

.inputbox {
  position: relative;
  margin: 10px 0;
  width: fill;
  border-bottom: 2px solid black;
}

.inputbox label {
  position: absolute;
  /* top: 50%; */
  left: 5px;
  transform: translateY(-50%);
  color: black;
  font-size: 1em;
  pointer-events: none;
  transition: .5s;
}

.input-type:focus~label,
.input-type:valid~label {
  top: -5px;
}

.inputbox input {
  width: 100%;
  height: 50px;
  background: transparent;
  border: none;
  outline: none;
  font-size: 1em;
  padding: 0 35px 0 5px;
  color: black;
}

.inputbox input[type="date"]::-webkit-calendar-picker-indicator {
  opacity: 0;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.inputbox ion-icon {
  position: absolute;
  right: 8px;
  color: black;
  font-size: 1.2em;
  top: 20px;
}

.forget {
  margin: -15px 0 15px;
  font-size: .9em;
  color: black;
  display: flex;
  justify-content: space-between;
}

.forget label input {
  margin-right: 3px;

}

.forget label a {
  color: black;
  text-decoration: none;
}

.forget label a:hover {
  text-decoration: underline;
}

.login-button {
  width: 100%;
  height: 40px;
  border-radius: 40px;
  background: black;
  border: none;
  outline: none;
  cursor: pointer;
  font-size: 1em;
  font-weight: 600;
}

.register {
  font-size: .9em;
  color: black;
  text-align: center;
  margin: 25px 0 10px;
}

.register p a {
  text-decoration: none;
  color: black;
  font-weight: 600;
}

.register p a:hover {
  text-decoration: underline;
}

.container {
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.padding-container {
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
  