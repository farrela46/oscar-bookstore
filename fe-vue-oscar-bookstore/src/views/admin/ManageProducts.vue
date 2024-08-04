<script>
// import { computed } from "vue";
import { useStore } from "vuex";
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
        categoriesName: []
      },
      showProductModal: false,
      selectedProduct: {},
      selectedCategoryId: null,
      selectedFile: '',
      loading: false,
      loadingRegist: false,
      dialog: false,
      showModal: false,
      selectedProductId: null,
      addProducts: false,
    };
  },
  computed: {
    formattedHarga: {
      get() {
        return this.buku.harga ? this.buku.harga.toLocaleString('id-ID') : '';
      },
      set(value) {
        const numericValue = parseInt(value.replace(/\./g, ''), 10);
        this.buku.harga = isNaN(numericValue) ? '' : numericValue;
      }
    }
  },
  methods: {
    closeModal() {
      document.getElementById('closeModal').click();
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    formatDate(data_date) {
      return moment.utc(data_date).format('YYYY-MM-DD')
    },
    async showProduct(slug) {
      this.selectedProduct = slug;

      try {
        const response = await axios.get(BASE_URL + '/buku/detail/' + slug, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.selectedProduct = response.data;
        let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('showProduct'))
        modal.show();
        // $('#showProduct').modal('show');
      } catch (error) {
        console.error(error);
      }
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
          params: {
            sortBy: 'lowstock'
          },
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.items = response.data;

        if (response.data.length > 0) {
          this.fotoUrl = response.data[0].foto;
        }
      } catch (error) {
        console.error('Error retrieving books:', error);
      } finally {
        this.loading = false;
      }
    },

    updateHarga(event) {
      const value = event.target.value.replace(/\D/g, '');
      this.buku.harga = parseInt(value, 10) || 0;
      this.formattedHarga = value ? parseInt(value, 10).toLocaleString('id-ID') : '';
    },
    async addBuku() {
      try {


        const formData = new FormData();

        formData.append('judul', this.buku.judul);
        formData.append('no_isbn', this.buku.no_isbn);
        formData.append('desc', this.buku.desc);
        formData.append('pengarang', this.buku.pengarang);
        formData.append('penerbit', this.buku.penerbit);
        formData.append('tahun_terbit', this.buku.tahun_terbit);
        formData.append('harga', this.buku.harga);
        formData.append('stok', this.buku.stok);
        this.buku.categoriesName.forEach((categoriesName) => {
          formData.append('categoryId[]', categoriesName);
        });
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

        this.addProducts = false;
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
    async deleteProduct(id) {
      try {
        const response = await axios.delete(`${BASE_URL}/buku/delete/` + id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
          },
        });
        console.log(response)
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Buku berhasil dihapus',
          color: 'green'
        });
        this.retrieveBuku();
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
      this.selectedFile = null;
      this.categoriesName = null;
      this.categories = []
    },
    openDeleteConfirmation(id) {
      this.selectedProductId = id;
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.show();
    },
    confirmDelete() {
      if (this.selectedProductId) {
        this.deleteProduct(this.selectedProductId);
        this.closeModalDelete();
      }
    },
    closeModalDelete() {
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.hide();
    },
    editUser(slug) {
      this.$router.push({ path: `/admin/products/` + slug })
    },
    goCategories() {
      console.log("Navigating to profile...");
      this.$router.push('/admin/categories')
    },
  },
  mounted() {
    const store = useStore();
    store.commit('toggleSidenav', true);
    this.retrieveBuku();
    this.retrieveCat();
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
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
                  <argon-button @click="addProducts = true">Add Products</argon-button>
                </div>
              </div>
              <v-dialog v-model="addProducts" max-width="600px">
                <v-card>
                  <v-card-title>
                    <span class="headline">Add Product</span>
                  </v-card-title>

                  <v-form @submit.prevent="addBuku">
                    <v-card-text>
                      <argon-input type="number" placeholder="No ISBN" v-model="buku.no_isbn" />
                      <argon-input type="text" placeholder="Judul Buku" v-model="buku.judul" />
                      <div class="form-floating mb-3">
                        <textarea class="form-control" v-model="buku.desc" placeholder="Deskripsi Buku"
                          id="floatingTextarea2" style="height: 100px"></textarea>
                        <label for="floatingTextarea2">Deskripsi Buku</label>
                      </div>
                      <argon-input type="text" placeholder="Pengarang" v-model="buku.pengarang" />
                      <argon-input type="text" placeholder="Penerbit" v-model="buku.penerbit" />
                      <argon-input type="date" placeholder="Tahun Terbit" v-model="buku.tahun_terbit" />
                      <input type="file" class="form-control" ref="fileInput" @change="handleFileChange">
                      <div class="mb-3">
                        <label class="form-label">Category</label>
                        <div class="form-check" v-for="category in categories" :key="category.id">
                          <input class="form-check-input" type="checkbox" :value="category.id"
                            v-model="buku.categoriesName">
                          <label class="form-check-label">{{ category.nama }}</label>
                        </div>
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp.</span>
                        <input type="text" class="form-control" v-model="formattedHarga" @input="updateHarga"
                          placeholder="Harga" aria-label="phone" aria-describedby="basic-addon1">
                      </div>
                      <argon-input type="number" placeholder="Stok" v-model="buku.stok" />

                      <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <button type="button" class="btn btn-secondary" text @click="addProducts = false">Close</button>
                      <button type="submit" class="ms-2 btn btn-primary">Add</button>
                    </v-card-actions>
                  </v-form>
                </v-card>
              </v-dialog>
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
                          Harga
                        </th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                          Stok
                        </th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                          Sold
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
                          <img
                            style="max-width: 100px; max-height: 134px; width: 100%; height: auto; object-fit: cover; overflow: hidden;"
                            :src="item.foto" class="rounded img-fluid" alt="Book Image">
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
                        <td>
                          <p class="text-xs text-secondary mb-0 text-center">Rp. {{ formatPrice(item.harga) }}</p>
                        </td>
                        <td>
                          <p class="text-xs text-secondary mb-0 text-center">{{ item.stok }}</p>
                        </td>
                        <td>
                          <p class="text-xs text-secondary mb-0 text-center">{{ item.sold }}</p>
                        </td>
                        <td class="align-middle">
                          <span class="" style="font-size: 1rem; cursor: pointer;" @click="showProduct(item.slug)">
                            <span style="color: blue;">
                              <i class="fas fa-eye"></i>
                            </span>
                          </span>
                          <span class="mx-3" style="font-size: 1rem; cursor: pointer;" @click="editUser(item.slug)">
                            <span style="color: green;">
                              <i class="fas fa-edit"></i>
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
                Are you sure you want to delete this Product?
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" @click="confirmDelete">Delete</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal fade text-black" id="showProduct" tabindex="-1" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Detail Product</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  id="closeModal"></button>
              </div>
              <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <div class="row">
                  <h2>{{ selectedProduct.judul }}</h2>
                </div>
                <hr>
                <div class="row d-flex justify-content-center align-items-center">
                  <div class="col-12 d-flex justify-content-center align-items-center" style="width: 400px">
                    <img :src="selectedProduct.foto" alt="Buku" style="max-width: 200px;">
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-12 mt-5">
                    <h5>Description : </h5>
                    <p>{{ selectedProduct.desc }}</p>
                    <hr>
                    <p>Category:</p>
                    <template v-if="selectedProduct.category">
                      <v-chip class="mx-2" v-for="(category, index) in selectedProduct.category.split(',')"
                        :key="index">
                        {{ category.trim() }}
                      </v-chip>
                    </template>
                    <hr>
                    <div class="row mt-3">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Harga:</label>
                          <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" :value="selectedProduct.harga" disabled>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Stok:</label>
                          <input type="text" class="form-control" :value="selectedProduct.stok" disabled>
                        </div>
                      </div>
                    </div>
                  </div>
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
    </div>
  </div>
</template>
