<script>
import setNavPills from '@/assets/js/nav-pills.js';
import setTooltip from '@/assets/js/tooltip.js';
import ArgonInput from '@/components/ArgonInput.vue';
import ArgonButton from '@/components/ArgonButton.vue';
import axios from 'axios';
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";

export default {
  name: 'Profile',
  components: {

    ArgonInput,
    ArgonButton,
    Navbar
  },
  data() {
    return {
      store: null,
      body: null,
      dialog: false,
      users: {
        name: '',
        email: '',
        no_telp: '',
        password: ''
      },
      loadingRegist: false,
      searchQuery: '',
      searchResults: [],
      selectedAddress: null,
      address: {
        provinsi: '',
        city: '',
        district: '',
        postal_code: '',
        penerima: '',
        no_penerima: '',
        label: '',
        alamat_lengkap: ''
      },
      alamat: [],
      selectedAddressId: null,
      confirmdeletion: false,
      overlay: false
    };
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  mounted() {
    setNavPills();
    setTooltip();
    this.getUser();
    this.fetchUserAddresses();
  },

  methods: {
    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = false;
      this.store.state.showFooter = false;
      this.body.classList.remove("bg-gray-100");
    },
    restorePage() {
      this.store.state.hideConfigButton = false;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
      this.store.state.showFooter = true;
      this.body.classList.add("bg-gray-100");
    },
    nextStep() {
      this.step++;
    },
    searchWithDelay() {
      this.loadingRegist = true;
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }
      this.searchTimeout = setTimeout(this.searchAddress, 2000);
    },
    async searchAddress() {
      if (this.searchQuery && this.searchQuery.length > 2) {
        try {
          const response = await axios.get(`${BASE_URL}/loc/areas`, {
            params: {
              countries: 'ID',
              input: this.searchQuery,
              type: 'single',
            },
          });
          this.searchResults = response.data.areas;
        } catch (error) {
          console.error('Error fetching address:', error);
          this.searchResults = [];
        } finally {
          this.loadingRegist = false
        }
      } else {
        this.searchResults = [];
      }
    },
    fillAddress() {
      if (this.selectedAddress) {
        this.address.provinsi = this.selectedAddress.administrative_division_level_1_name;
        this.address.city = this.selectedAddress.administrative_division_level_2_name;
        this.address.district = this.selectedAddress.administrative_division_level_3_name;
        this.address.postal_code = this.selectedAddress.postal_code;
      }
    },
    async saveAddress() {
      const addressData = {
        selected_address_id: this.selectedAddress.id,
        name: this.selectedAddress.name,
        provinsi: this.selectedAddress.administrative_division_level_1_name,
        kota: this.selectedAddress.administrative_division_level_2_name,
        kecamatan: this.selectedAddress.administrative_division_level_3_name,
        postal_code: this.selectedAddress.postal_code,
        penerima: this.address.penerima,
        alamat_lengkap: this.address.alamat_lengkap,
        no_penerima: this.address.no_penerima,
        label: this.address.label,
      };

      try {
        await axios.post(`${BASE_URL}/address/store`, addressData, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.dialog = false,
          this.$notify({
            type: 'success',
            title: 'Success',
            text: 'Alamat berhasil ditambah',
            color: 'green'
          });
        this.resetForm();
        this.fetchUserAddresses();
      } catch (error) {
        console.error('Error saving address:', error);
      }
    },
    resetForm() {
      this.searchQuery = '';
      this.searchResults = [];
      this.selectedAddress = null;
      this.address = {
        provinsi: '',
        kota: '',
        kecamatan: '',
        postal_code: '',
        alamat_lengkap: '',
        penerima: '',
        no_penerima: '',
        label: ''
      };
    },
    cancelAlamat() {
      this.dialog = false,
        this.resetForm();
    },
    async fetchUserAddresses() {
      this.overlay = true
      try {
        const response = await axios.get(`${BASE_URL}/address/get`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        });
        this.alamat = response.data.addresses;
      } catch (error) {
        console.error('Error fetching addresses:', error);
      } finally {
        this.overlay = false
      }
    },
    async deleteAddress(id) {
      try {
        const response = await axios.delete(`${BASE_URL}/address/` + id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
          },
        });
        console.log(response)
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Addresss berhasil dihapus',
          color: 'green'
        });
        this.fetchUserAddresses();
      } catch (error) {
        console.error(error);
      }
    },
    openDeleteConfirmation(id) {
      this.selectedAddressId = id;
      this.confirmdeletion = true
    },
    confirmDelete() {
      if (this.selectedAddressId) {
        this.deleteAddress(this.selectedAddressId);
        this.confirmdeletion = false
      }
    },
    async getUser() {
      this.overlay = true
      try {
        const response = await axios.get(`${BASE_URL}/user`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.users = response.data.user;
      } catch (error) {
        console.error(error);

        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          console.log(errorMessage)
        }
      } finally {
        this.overlay = false
      }
    },
    async updateUser() {
      try {
        const response = await axios.put(`${BASE_URL}/user/update`, this.users, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.users.password = null,
          console.log(response.data.message);
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Profil berhasil di Update',
          color: 'green'
        });
      } catch (error) {
        console.error(error);
        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          console.log(errorMessage);
        }
      }
    },
    async onLogout() {
      try {
        await axios.post(`${BASE_URL}/logout`, {}, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token'),
          }
        });

        localStorage.removeItem('access_token');
        this.$router.push('/login');
      } catch (error) {
        console.error('Logout failed:', error);
      }
    }
  },
};
</script>

<template>
  <div class="border-main">

    <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
    <div class="container-fluid">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
      <div class="card shadow-lg" style="margin-top: 30px;">
        <div class="card-body p-3">
          <div class="row gx-4">
            <div class="col-auto">
              <div class="avatar avatar-xl position-relative">
                <img src="../assets/img/team-1.jpg" alt="profile_image" class="shadow-sm w-100 border-radius-lg" />
              </div>
            </div>
            <div class="col-auto my-auto">
              <div class="h-100">
                <h5 class="mb-1">{{ users.name }}</h5>
                <p class="mb-0 font-weight-bold text-sm">{{ users.role }}</p>
              </div>
            </div>
            <div class="mx-auto mt-3 col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0">
              <div class="nav-wrapper position-relative end-0">
                <argon-button color="danger" @click="onLogout"><span class="mx-3"
                    style="font-size: 1rem; cursor: pointer;">
                    <span style="color: WHITE;">
                      <i class="fas fa-running"></i>
                    </span>
                  </span>Logout</argon-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="py-4 container-fluid">
      <div class="row">
        <div class="col-md-7">
          <div class="card shadow-lg">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Edit Profile</p>
              </div>
            </div>
            <div class="card-body">
              <p class="text-uppercase text-sm">User Information</p>
              <div class="row">
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">Username</label>
                  <argon-input v-model="users.name" type="text" />
                </div>
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">Email address</label>
                  <argon-input v-model="users.email" type="email" />
                </div>
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">Nomor Telepon</label>
                  <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">+62</span>
                    <input
                      oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                      type="number" maxlength="13" class="form-control" v-model="users.no_telp"
                      placeholder="Phone Number" aria-label="phone" aria-describedby="basic-addon1">
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="example-text-input" class="form-control-label">Password</label>
                  <input type="password" class="form-control" v-model="users.password">
                </div>
              </div>
              <hr class="horizontal dark" />
              <div class="col-md-12 text-end">
                <argon-button color="success" size="sm" class="ms-auto" @click="updateUser">Update</argon-button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-5">
          <div class="card shadow-lg">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Alamat</p>
              </div>
            </div>
            <div class="card-body">
              <p class="text-uppercase text-sm">Alamat Pengguna &nbsp;<span>
                  <!-- ({{ alamat.length }}/3) -->
                </span></p>
              <div class="row">
                <argon-button @click="dialog = true" color="success" size="sm" class="ms-auto"><i
                    class="fas fa-plus"></i>&nbsp;Alamat</argon-button>
              </div>
              <hr class="horizontal dark" />
              <div class="row">
                <div v-for="item in alamat" :key="item.id" class="col-md-12 mb-3">
                  <div class="border p-2 rounded-lg">
                    <div class="row">
                      <div class="col">
                        <v-chip color="green" variant="elevated">
                          {{ item.label }}
                        </v-chip>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-9 col-9">
                        <div class="mt-2">
                          <a class="card-alamat" style="color: grey;">{{ item.penerima }}</a>
                          <br>
                          <a class="card-alamat" style="color: grey;">{{ item.alamat_lengkap }}</a>
                          <br>
                          <a class="card-alamat" style="color: grey;">{{ item.name }}</a>
                          <br>
                          <a class="card-alamat" style="color: grey;">+62&nbsp;{{ item.no_penerima }}</a>
                        </div>
                      </div>
                      <div class="col-md-3 col-3 d-flex align-items-center justify-content-center ">
                        <i class="fas fa-trash fa-lg" style="color: #ff0000; cursor: pointer"
                          @click="openDeleteConfirmation(item.id)"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <v-dialog v-model="dialog" persistent max-width="600px">
        <v-card>
          <v-card-title>
            <span class="headline">Tambah Alamat</span>
          </v-card-title>

          <v-card-text>
            <div class="mb-3">
              <label for="searchQuery" class="form-label">Cari Lokasi</label>
              <input type="text" class="form-control" id="searchQuery" v-model="searchQuery" @input="searchWithDelay">
            </div>

            <div v-if="searchResults.length" class="mb-3">
              <label for="addressSelect" class="form-label">Pilih Alamat</label>
              <select class="form-select" id="addressSelect" v-model="selectedAddress" @change="fillAddress">
                <option v-for="result in searchResults" :key="result.id" :value="result">{{ result.name }}</option>
              </select>
            </div>
            <div v-else><a style="font-size: 12px; color:red;"><i class="fas fa-info-circle"
                  style="color: #ff0000;"></i>&nbsp;Jika Alamat yang dicari tidak muncul, coba ganti kata kunci atau
                input kode pos!</a></div>
            <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
            <div class="wrap" v-if="selectedAddress">
              <form>
                <div class="mb-3">
                  <label for="Province" class="form-label">Provinsi</label>
                  <input type="text" class="form-control" id="province" v-model="address.provinsi">
                </div>
                <div class="mb-3">
                  <label for="City" class="form-label">Kota</label>
                  <input type="text" class="form-control" id="city" v-model="address.city">
                </div>
                <div class="mb-3">
                  <label for="District" class="form-label">Kecamatan</label>
                  <input type="text" class="form-control" id="district" v-model="address.district">
                </div>
                <div class="mb-3">
                  <label for="postal code" class="form-label">Kode Pos</label>
                  <input type="text" class="form-control" id="district" v-model="address.postal_code">
                </div>
                <div class="mb-3">
                  <label for="postal code" class="form-label">Alamat Lengkap</label>
                  <textarea type="text" class="form-control" id="district" v-model="address.alamat_lengkap"></textarea>
                </div>
                <hr class="horizontal dark" />
                <div class="mb-3">
                  <label for="recepient" class="form-label">Penerima</label>
                  <input type="text" class="form-control" id="recipientPhone" v-model="address.penerima">
                </div>
                <div class="mb-3">
                  <label for="recipientPhone" class="form-label">Nomor Telepon Penerima</label>
                  <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">+62</span>
                    <input type="text" class="form-control" v-model="address.no_penerima" placeholder="Phone Number"
                      aria-label="phone" aria-describedby="basic-addon1">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="addressNote" class="form-label">Label Alamat</label>
                  <input type="text" class="form-control" id="addressNote" placeholder="Rumah, Kantor"
                    v-model="address.label">
                </div>
              </form>
            </div>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" text @click="cancelAlamat">Batal</v-btn>
            <v-btn color="blue darken-1" text @click="saveAddress">Simpan</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog v-model="confirmdeletion" max-width="600px">
        <v-card>
          <v-card-title class="headline">Confirmation</v-card-title>
          <v-card-text>
            Are you sure you want to delete this Address?
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="red darken-1" text @click="confirmdeletion = false">Batal</v-btn>
            <v-btn color="green darken-1" text @click="confirmDelete">Saya Setuju</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>
<style scoped>
.border-main {
  background-color: #42BADB;
  height: 20vh;
}

.card-alamat {
  font-size: 18px;
}
</style>
