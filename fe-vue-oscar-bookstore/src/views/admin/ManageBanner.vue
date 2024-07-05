<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
// import ArgonPagination from "@/components/ArgonPagination.vue";
// import ArgonPaginationItem from "@/components/ArgonPaginationItem.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import moment from 'moment';
import * as bootstrap from 'bootstrap';
import Navbar from "@/examples/Navbars/Navbar.vue";



export default {
  components: {
    // ArgonPagination,
    // ArgonPaginationItem,
    ArgonButton,
    ArgonInput,
    Navbar
  },
  data() {
    return {
      reviews: [],
      banner: {
        judul: '',
        foto: null,
      },
      banners: [],
      loading: false,
      loadingRegist: false,
      addBanner: false,
      overlay: false
    };
  },
  computed: {
    isMobile() {
      return window.innerWidth <= 768;
    }
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    formatDate(data_date) {
      return moment.utc(data_date).format('YYYY-MM-DD')
    },
    formatDateCourier(date) {
      if (!date) return "";
      const options = { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
      return new Date(date).toLocaleDateString('id-ID', options);
    },
    closeModal() {
      document.getElementById('closeModal').click();
    },
    handleFileChange(event) {
      this.banner.foto = event.target.files[0];
    },
    async storeBanner() {
      this.loadingRegist = true
      try {

        const formData = new FormData();
        formData.append('judul', this.banner.judul);
        if (this.banner.foto) {
          formData.append('foto', this.banner.foto, this.banner.foto.name);
        }

        const token = localStorage.getItem('access_token');
        const response = await axios.post(`${BASE_URL}/banner/store`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': 'Bearer ' + token,
          },
        });

        console.log(response.data);

        this.addBanner = false;
        this.getBanner();
        this.clearForm();

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Banner berhasil ditambahkan',
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
      } finally {
        this.loadingRegist = false
      }
    },
    async getBanner() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/banner/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.banners = response.data;
      } catch (error) {
        console.error(error);
      } finally {
        this.overlay = false;
      }
    },
    async deleteUser(id) {
      try {
        const response = await axios.delete(`${BASE_URL}/deleteUser/` + id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
          },
        });
        console.log(response)
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'User berhasil dihapus',
          color: 'green'
        });
        this.getAllUser();
      } catch (error) {
        console.error(error);
      }
    },
    clearForm() {
      this.banner.judul = '';
      this.banner.foto = null;
    },
    openDeleteConfirmation(id) {
      this.selectedUserId = id;
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.show();
    },
    confirmDelete() {
      if (this.selectedUserId) {
        this.deleteUser(this.selectedUserId);
        this.closeModalDelete();
      }
    },
    closeModalDelete() {
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.hide();
    },
    editUser(id_user) {
      let obj = this.users.find(o => o.id === id_user);
      this.users_edit = obj;
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'))
      modal.show();
    },
  },
  mounted() {
    this.getBanner();
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <div class="py-4 container-fluid">
    <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
      <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
    </v-overlay>
    <div class="container">
      <div class="row ps-3 mb-2  text-end bg-white p-2" style="border-radius: 10px">
        <div class="col text-start d-flex align-items-center">
          <h5><strong>List Banner</strong></h5>
        </div>
        <div class="col">
          <argon-button @click="addBanner = true">Add Banner</argon-button>
        </div>
      </div>

      <div class="row">
        <div class="mb-2 card" v-for="(item, index) in banners" :key="index" style="max-height: 400px">
          <div class="card-body">
            <div class="row">
              <div class="col-md-10 col-sm-10">
                <div class="row">
                  <h4> {{ item.judul }} </h4>
                </div>
                <div class="row">
                  <img :src="item.foto" class="rounded img-fluid" alt="Book Image" style="width: 100%;
  max-height: 300px;
  object-fit: cover;
  border-radius: 5px;">
                </div>
              </div>
              <div class="col-md-2 col-sm-2 d-flex align-items-center justify-content-center">
                <argon-button @click="addBanner = true" color="danger">Hapus</argon-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- <div class="row mt-2">
        <argon-pagination>
          <argon-pagination-item prev />
          <argon-pagination-item label="1" active />
          <argon-pagination-item label="2" disabled />
          <argon-pagination-item label="3" />
          <argon-pagination-item next />
        </argon-pagination>
      </div> -->
    </div>
    <v-dialog v-model="addBanner" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="headline">Add Banner</span>
        </v-card-title>

        <v-form @submit.prevent="storeBanner"> <!-- Handle submit event -->
          <v-card-text>
            <argon-input type="text" placeholder="Judul Banner" v-model="banner.judul" />
            <input type="file" class="form-control" ref="fileInput" @change="handleFileChange">
            <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <button type="button" class="btn btn-secondary" @click="addBanner = false">Close</button>
            <button type="submit" class="ms-2 btn btn-primary">Add</button> <!-- Use type="submit" -->
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-black" id="deleteConfirmationModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this user?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="confirmDelete">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.mobile-text {
  font-size: 16px;
}

.author-text {
  font-size: 12px;
  font-weight: 300;
}

@media (max-width: 768px) {
  .mobile-text {
    font-size: 16px !important;
  }

  .author-text {
    font-size: 12px !important;
    font-weight: 300 !important;
  }
}
</style>
