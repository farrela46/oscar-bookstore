<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import ArgonPagination from "@/components/ArgonPagination.vue";
import ArgonPaginationItem from "@/components/ArgonPaginationItem.vue";
// import ArgonButton from "@/components/ArgonButton.vue";
// import ArgonInput from "@/components/ArgonInput.vue";
import moment from 'moment';
import * as bootstrap from 'bootstrap';
import Navbar from "@/examples/Navbars/Navbar.vue";



export default {
  components: {
    ArgonPagination,
    ArgonPaginationItem,
    // ArgonButton,
    // ArgonInput,
    Navbar
  },
  data() {
    return {
      reviews: [],
      users_edit: [],
      register: {
        name: '',
        email: '',
        password: '',
      },
      loading: false,
      loadingRegist: false,
      dialog: false,
      showModal: false,
      selectedUserId: null,
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
    async getAllReview() {
      this.loading = true;
      try {
        const response = await axios.get(`${BASE_URL}/review/all`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.reviews = response.data.data;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async onSubmit() {
      this.loadingRegist = true;
      try {
        const response = await axios.post(`${BASE_URL}/register`, {
          name: this.register.name,
          email: this.register.email,
          password: this.register.password
        });
        this.getAllUser();
        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green'
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
      } finally {
        this.loadingRegist = false;
        this.clearForm();
        this.closeModal();
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
      this.register.name = '';
      this.register.email = '';
      this.register.password = '';
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
    this.getAllReview();
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="mb-2 card" v-for="(item, index) in reviews" :key="index">
        <div class="card-body">
          <h6 class="card-title">Pesanan {{ index + 1 }}</h6>
          <div class="row">
            <div class="col-md-3 col-4">
              <div class="row">
                <div class="col">
                  <img :src="item.buku.foto" class="img-fluid" alt="Book image"
                    :style="{ maxWidth: isMobile ? '80px' : '100px' }">
                </div>
              </div>
            </div>
            <div class="col-md-9 col-8">
              <div class="row">
                <div class="col-12">
                  <div class="row">
                    <a class="text-truncate text-bold" :class="{ 'mobile-text': isMobile }" style="color: black;">
                      {{ item.buku.judul }}
                    </a>
                  </div>
                  <div class="row">
                    <div style="color: black">
                      <div class="row mt-2">
                        <div class="col-12 border" style="border-radius: 10px;">
                          <div class="px-4">
                            <div class="row">
                              <v-rating class="mt-2" density="compact" readonly v-model="item.rating"
                                active-color="yellow" color="grey"></v-rating>
                              <div class="row mt-2">
                                <p :class="{ 'mobile-text': isMobile }" class="text-black">{{ item.comment }}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                  <span><strong>Rp {{ formatPrice(item.buku.harga) }}</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-2">
      <argon-pagination>
        <argon-pagination-item prev />
        <argon-pagination-item label="1" active />
        <argon-pagination-item label="2" disabled />
        <argon-pagination-item label="3" />
        <argon-pagination-item next />
      </argon-pagination>
    </div>
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
  font-size: 12px;
}

@media (max-width: 768px) {
  .mobile-text {
    font-size: 12px !important;
  }
}
</style>
