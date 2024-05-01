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
      users: [],
      users_edit: [],
      items: [],
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
        categoriesId:''
      },
      selectedFile: [],
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
      }
    },
    async retrieveBuku() {
      this.loading = true;
      try {
        const response = await axios.get(`${BASE_URL}/buku/get`, {
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
    async addBuku() {
      try {
        const formData = new FormData();

        // Append other fields to formData
        formData.append('judul', this.buku.judul);
        formData.append('no_isbn', this.buku.no_isbn);
        formData.append('desc', this.buku.desc);
        formData.append('pengarang', this.buku.pengarang);
        formData.append('penerbit', this.buku.penerbit);
        formData.append('tahun_terbit', this.buku.tahun_terbit);
        formData.append('harga', this.buku.harga);
        formData.append('stok', this.buku.stok);

        if (this.buku.foto) {
          formData.append('foto', this.buku.foto, this.buku.foto.name);
        }


        const token = localStorage.getItem('access_token');
        const response = await axios.post(BASE_URL + '/buku/add', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': 'Bearer ' + token,
          },
        });

        console.log(response.data);

        this.closeModal();
        this.retrieveBuku();
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

    handleFileChange(event) {
      this.buku.foto = event.target.files[0];
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
    handleclose() {
      this.clearForm();
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
      this.buku.foto = null;
      this.selectedFile = '';
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
    goCategories() {
      console.log("Navigating to profile...");
      this.$router.push('/admin/categories')
    },
  },
  mounted() {
    this.retrieveBuku();
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
                <h6 class="mb-0">List Products</h6>
                <div class="div">
                  <v-tooltip text="Categories Setting" location="start">
                    <template v-slot:activator="{ props }">
                      <span v-bind="props" class="mx-3" style="font-size: 1.5rem; cursor: pointer;"
                        @click="goCategories">
                        <span style="color: #2DCE89;">
                          <i class="fas fa-cog"></i>
                        </span>
                      </span>
                    </template>
                  </v-tooltip>
                  <argon-button data-bs-toggle="modal" data-bs-target="#exampleModal">Add Products</argon-button>
                </div>
              </div>
              <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title text-black" id="userModalLabel">Add Products</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeModal"
                        @click="handleclose"></button>
                    </div>
                    <form role="form" @submit.prevent="addBuku">
                      <div class="modal-body">
                        <argon-input type="text" placeholder="No ISBN" v-model="buku.no_isbn" />
                        <argon-input type="text" placeholder="Judul Buku" v-model="buku.judul" />
                        <argon-input type="text-area" placeholder="Deskripsi Buku" v-model="buku.desc" />
                        <argon-input type="text" placeholder="Pengarang" v-model="buku.pengarang" />
                        <argon-input type="text" placeholder="Penerbit" v-model="buku.penerbit" />
                        <argon-input type="date" placeholder="Tahun Terbit" v-model="buku.tahun_terbit" />
                        <input type="file" class="form-control" ref="fileInput" @change="handleFileChange" multiple>
                        <select class="form-select mt-3" v-model="buku.categoryId">
                          <option value="" disabled>Select Category</option>
                          <option v-for="category in categories" :key="category.id" :value="category.id">{{
                      category.nama }}</option>
                        </select>
                        <argon-input class="mt-3" type="text" placeholder="Harga" v-model="buku.harga" />
                        <argon-input type="text" placeholder="Stok" v-model="buku.stok" />
                      </div>
                      <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                          @click="handleclose">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
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
                          Images
                        </th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                          Title
                        </th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                          Author
                        </th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                          Penerbit
                        </th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                          Tahun Terbit
                        </th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
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
                          <img style="max-width: 100px;" :src="item.foto" class="rounded img-fluid" alt="Book Image">
                        </td>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm">{{ item.judul }}</h6>
                              <p class="text-xs text-secondary mb-0">
                                {{ item.no_isbn }}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs text-secondary mb-0">{{ item.pengarang }}</p>
                        </td>
                        <td>
                          <p class="text-xs text-secondary mb-0 text-center">{{ item.penerbit }}</p>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-secondary text-xs font-weight-bold">{{ formatDate(item.tahun_terbit)
                            }}</span>
                        </td>
                        <td class="align-middle">
                          <span class="mx-3" style="font-size: 1rem; cursor: pointer;" @click="editUser(item.id)">
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
                Are you sure you want to delete this user?
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
                <h5 class="modal-title text-black" id="userModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  id="closeModal"></button>
              </div>
              <form role="form" @submit.prevent="userUpdate">
                <div class="modal-body">
                  <argon-input type="text" placeholder="Name" v-model="users_edit.name" />
                  <argon-input type="email" placeholder="Email" v-model="users_edit.email" />
                  <argon-input type="password" placeholder="Password" v-model="users_edit.password" />
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
