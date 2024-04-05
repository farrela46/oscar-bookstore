<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import ArgonPagination from "@/components/ArgonPagination.vue";
import ArgonPaginationItem from "@/components/ArgonPaginationItem.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import moment from 'moment';
import * as bootstrap from 'bootstrap';



export default {
  components: {
    ArgonPagination,
    ArgonPaginationItem,
    ArgonButton,
    ArgonInput
  },
  data() {
    return {
      items_edit: [],
      items: [],
      categories: '',
      loading: false,
      loadingRegist: false,
      dialog: false,
      showModal: false,
      selectedUserId: null,
    };
  },
  methods: {
    closeModal() {
      document.getElementById('closeModal').click();
    },
    formatDate(data_date) {
      return moment.utc(data_date).format('YYYY-MM-DD')
    },
    async getAllUser() {
      this.loading = true;
      try {
        const response = await axios.get(`${BASE_URL}/getUser`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.users = response.data;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async retrieveCat() {
      this.loading = true;
      try {
        const response = await axios.get(`${BASE_URL}/category/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.items = response.data

        if (response.data.length > 0) {
          this.fotoUrl = response.data[0].foto;
        }
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async onSubmit() {
      this.loadingRegist = true;
      try {
        const response = await axios.post(`${BASE_URL}/category/add`, {
          nama: this.categories,
        }, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          },
        });

        this.retrieveCat();

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
    async catUpdate(id) {
      this.loadingRegist = true;
      console.log(id)
      try {
        const response = await axios.post(`${BASE_URL}/category/update/` +  id, {
          nama: this.items_edit.nama,
        }, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          },
        });
        console.log('User updated:', response.data);
        this.closeModalEdit();
        this.retrieveCat();
        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green'
        });
      } catch (error) {
        console.error('Update failed:', error);

      } finally {
        this.loadingRegist = false;
      }
    },
    async deleteCat(id) {
      try {
        const response = await axios.delete(`${BASE_URL}/category/delete/` + id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
          },
        });
        console.log(response)
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Category berhasil dihapus',
          color: 'green'
        });
        this.retrieveCat();
      } catch (error) {
        console.error(error);
      }
    },
    clearForm() {
      this.categories = '';
    },
    openDeleteConfirmation(id) {
      this.selectedCatId = id;
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.show();
    },
    confirmDelete() {
      if (this.selectedCatId) {
        this.deleteCat(this.selectedCatId);
        this.closeModalDelete();
      }
    },
    closeModalEdit() {
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'))
      modal.hide();
    },
    closeModalDelete() {
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.hide();
    },
    editCat(id_cat) {
      let obj = this.items.find(o => o.id === id_cat);
      this.items_edit = obj;
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'))
      modal.show();
      console.log(id_cat)
    },
  },
  mounted() {
    this.retrieveCat();
  },
};
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="row">
          <div class="col-md-12 col-12">
            <div class="card">
              <div class="card-header pb-0 d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">List Categories</h6>
                <div class="div">
                  <argon-button data-bs-toggle="modal" data-bs-target="#exampleModal">Add Categories</argon-button>
                </div>
              </div>
              <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title text-black" id="userModalLabel">Add Categories</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="closeModal"></button>
                    </div>
                    <form role="form" @submit.prevent="onSubmit">
                      <div class="modal-body">
                        <argon-input type="text" placeholder="Name" v-model="categories" />
                      </div>
                      <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="card-body px-0 pt-0 pb-2">
                <div v-if="loading" class="divider">
                  <v-progress-linear indeterminate></v-progress-linear>
                </div>
                <div v-else class="table-responsive p-0">
                  <table class="table align-items-center mb-0">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7">
                          No
                        </th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                          Categories
                        </th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                          Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in items" :key="index">
                        <td>
                          <div class="px-2 py-1">
                            <div class="d-flex justify-content-center">
                              <h6 class="mb-0 text-sm">{{ index + 1 }}</h6>
                            </div>
                          </div>
                        </td>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm">{{ item.nama }}</h6>
                            </div>
                          </div>
                        </td>
                        <td class="align-middle">
                          <span class="mx-3" style="font-size: 1rem; cursor: pointer;" @click="editCat(item.id)">
                            <span style="color: green;">
                              <i class="fa fa-pencil-square-o"></i>
                            </span>
                          </span>
                          <span style="font-size: 1rem; cursor: pointer;" @click="openDeleteConfirmation(item.id)">
                            <span style="color: red;">
                              <i class="fa fa-trash"></i>
                            </span>
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
          aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title text-black" id="deleteConfirmationModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                Are you sure you want to delete this category?
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" @click="confirmDelete">Delete</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title text-black" id="userModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  id="closeModal"></button>
              </div>
              <form role="form" @submit.prevent="catUpdate(items_edit.id)">
                <div class="modal-body">
                  <argon-input type="text" placeholder="Name" v-model="items_edit.nama" />
                </div>
                <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
              </form>
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
      </div>
    </div>
  </div>
</template>
