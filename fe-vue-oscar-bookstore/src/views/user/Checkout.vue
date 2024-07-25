<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      orders: [],
      totalPayment: '',
      alamat: [],
      loadingRegist: false,
      loadingKurir: false,
      searchQuery: '',
      searchResults: [],
      selectedAddresses: null,
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
      kirim: {
        provinsi: '',
        kota: '',
        kecamatan: '',
        postal_code: '',
        penerima: '',
        no_penerima: ''
      },
      users: {},
      selectedAddressId: null,
      selectedAddress: {},
      shippingRates: [],
      dialog: false,
      selectedCourier: null,
    };
  },

  mounted() {
    this.retrieveCart();
    this.fetchUserAddresses();
    this.getUser()
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  watch: {
    selectedAddressId() {
      this.fillAddress();
      this.fetchShippingRates();
    },
    selectedCourier() {
      this.updateTotalPayment();
    }
  },
  methods: {
    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = false;
      this.store.state.showFooter = false;
      this.body.classList.remove("bg-gray-100");
    },
    async getUser() {
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
      }
    },
    restorePage() {
      this.store.state.hideConfigButton = false;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
      this.store.state.showFooter = true;
      this.body.classList.add("bg-gray-100");
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    updateTotalPayment() {
      this.totalPayment = this.orders.reduce((total, order) => {
        return order.selected ? total + order.totalPrice : total;
      }, 0);
    },
    selectCourier(rate) {
      this.selectedCourier = rate;
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
    filledAddress() {
      if (this.selectedAddresses) {
        this.address.provinsi = this.selectedAddresses.administrative_division_level_1_name;
        this.address.city = this.selectedAddresses.administrative_division_level_2_name;
        this.address.district = this.selectedAddresses.administrative_division_level_3_name;
        this.address.postal_code = this.selectedAddresses.postal_code;
      }
    },
    async saveAddress() {
      const addressData = {
        selected_address_id: this.selectedAddresses.id,
        name: this.selectedAddresses.name,
        provinsi: this.selectedAddresses.administrative_division_level_1_name,
        kota: this.selectedAddresses.administrative_division_level_2_name,
        kecamatan: this.selectedAddresses.administrative_division_level_3_name,
        postal_code: this.selectedAddresses.postal_code,
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
    async proceedToCheckout() {
      if (!this.selectedCourier) {
        this.$notify({
          type: 'warning',
          title: 'Gagal',
          text: 'Kurir harus dipilih',
          color: 'green'
        });
        return;
      }

      this.overlay = true;
      try {
        const response = await axios.post(`${BASE_URL}/order/checkout`, {
          amount: this.totalPayment,
          selectedCourier: this.selectedCourier,
          address_id: this.selectedAddress.id,
          items: this.orders.map(order => ({
            buku_id: order.buku.id,
            quantity: order.quantity,
            totalPrice: order.totalPrice,
          })),
          first_name: this.users.name,
          last_name: '',
          email: this.users.email,
          phone: this.users.no_telp,
        }, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'application/json',
          },
        });

        const { paymentUrl } = response.data;
        window.open(paymentUrl,"_self");
        setTimeout(() => {
          this.$router.push('/orders');
        }, 1000);
      } catch (error) {
        console.error('Error proceeding to checkout:', error);
        const errorMessage = error.response.data.error;
          this.$notify({
            type: 'error',
            title: 'Gagal',
            text: errorMessage,
            color: 'red'
          });
      } finally {
        this.overlay = false;
      }
    },

    async fetchShippingRates() {
      this.loadingKurir = true;
      if (!this.selectedAddressId) {
        return;
      }

      const payload = {
        destination_area_id: this.selectedAddressId,
        items: this.orders.map((order) => ({
          name: order.buku.judul,
          description: order.buku.desc,
          value: order.buku.harga,
          length: 30,
          width: 2,
          height: 20,
          weight: 150,
          quantity: order.quantity,
        })),
      };

      try {
        const response = await axios.post(`${BASE_URL}/cart/rates`, payload, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'application/json',
          },
        });

        this.shippingRates = response.data.data.pricing;
      } catch (error) {
        console.error('Error fetching shipping rates:', error);
      } finally {
        this.loadingKurir = false
      }
    },
    fillAddress() {
      this.selectedAddress = this.alamat.find(address => address.selected_address_id === this.selectedAddressId) || {};
    },
    async fetchUserAddresses() {
      try {
        const response = await axios.get(`${BASE_URL}/address/get`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        });
        this.alamat = response.data.addresses;
      } catch (error) {
        console.error('Error fetching addresses:', error);
      }
    },
    async updateQuantity(index, id, newQuantity) {
      if (newQuantity < 1) return;

      try {
        await axios.put(`${BASE_URL}/cart/update/` + id, { quantity: newQuantity }, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.orders[index].quantity = newQuantity;
        this.orders[index].totalPrice = newQuantity * this.orders[index].harga;

        this.retrieveCart();
      } catch (error) {
        console.error(error);
      }
    },
    async retrieveCart() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/cart/checkout`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.orders = response.data.data
        this.totalPayment = response.data.totalPayment;
      } catch (error) {
        console.error(error);
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Anda belum menambahkan barang',
          color: 'green'
        });
      } finally {
        this.overlay = false;
      }
    },
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <div class="py-4 container">
    <div class="row mt-3">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
      <div class="container">
        <h3>Checkout</h3>
        <div class="row">
          <div class="col-lg-8">
            <div class="row mb-2">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Pilih Alamat <i class="fas fa-plus fa-md mx-4"
                      style="color: #5e72e4; cursor:pointer" @click="dialog = true"></i></h5>
                  <div class="row">
                    <select class="form-select" aria-label="Default select example" v-model="selectedAddressId"
                      @change="fillAddress">
                      <option value="" disabled>Pilih alamat</option>
                      <option v-for="item in alamat" :key="item.selected_address_id" :value="item.selected_address_id">
                        {{ item.penerima }} || {{ item.name }}
                      </option>
                    </select>
                  </div>
                  <div v-if="selectedAddressId">
                    <hr class="horizontal dark">
                    <div class="row">
                      <div class="col-md-6 col">
                        <span><strong>Nama Penerima</strong></span>
                        <p>{{ selectedAddress.penerima }}</p>
                      </div>
                      <div class="col-md-6 col">
                        <span><strong>Nomor Penerima</strong></span>
                        <p>{{ selectedAddress.no_penerima }}</p>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 col">
                        <span><strong>Provinsi</strong></span>
                        <p>{{ selectedAddress.provinsi }}</p>
                      </div>
                      <div class="col-md-6 col">
                        <span><strong>Kota</strong></span>
                        <p>{{ selectedAddress.kota }}</p>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 col">
                        <span><strong>Kecamatan</strong></span>
                        <p>{{ selectedAddress.kecamatan }}</p>
                      </div>
                      <div class="col-md-6 col">
                        <span><strong>Kode Pos</strong></span>
                        <p>{{ selectedAddress.postal_code }}</p>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 col">
                        <span><strong>Alamat Lengkap</strong></span>
                        <p>{{ selectedAddress.alamat_lengkap }}</p>
                      </div>
                      <div class="col-md-6 col">
                        <span><strong>Label</strong></span>
                        <p>{{ selectedAddress.label }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row mb-2" v-if="selectedAddressId">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Pilih Kurir</h5>
                  <v-progress-linear v-if="loadingKurir" indeterminate></v-progress-linear>
                  <div v-else v-for="(rate, index) in shippingRates" :key="index" class="row border mb-3"
                    style="border-radius: 10px;">
                    <div class="col-sm-12">
                      <div class="p-1">
                        <div class="row align-items-center py-2">
                          <div class="col-md-1 col d-flex align-items-center">
                            <input type="checkbox" class="large-checkbox" :checked="selectedCourier === rate"
                              @change="selectCourier(rate)" />
                          </div>
                          <div class="col-md-2 col d-flex align-items-center">
                            <img v-if="rate.company === 'jne'" src="../../assets/img/jne.png" alt="jne"
                              class="img-fluid" style="width: 50px; margin-right: 20px;" />
                            <img v-if="rate.company === 'sicepat'" src="../../assets/img/sicepat.png" alt="sicepat"
                              class="img-fluid" style="width: 50px; margin-right: 20px;" />
                          </div>
                          <div class="col-md-3 col">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Jenis Layanan</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">{{ rate.courier_name }} {{ rate.courier_service_name }}</a>
                            </div>
                          </div>
                          <div class="col-md-4 col">
                            <div class="row">
                              <strong class="d-block d-sm-inline text-truncate">Estimasi Pengiriman</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">{{ rate.duration }}</a>
                            </div>
                          </div>
                          <div class="col-md-2 col">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Tarif</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">Rp. {{ formatPrice(rate.price) }}</a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div v-for="(order, index) in orders" :key="index" class="mb-2 card">
                <div class="card-body">
                  <h5 class="card-title">Pesanan {{ index + 1 }}</h5>
                  <div class="row">
                    <div class="col-md-3 col-4">
                      <div class="row">
                        <div class="col">
                          <img :src="order.buku.foto" class="img-fluid" alt="Book image" style="max-width: 140px;
    max-height: 187px;
    width: 100%;
    height: auto;
    object-fit: cover;
    overflow: hidden;">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9 col-8">
                      <div class="row">
                        <div class="col-12">
                          <h5 class="text-truncate">{{ order.buku.judul }}</h5>
                          <p class="d-inline"><span class="mx-2">{{ order.quantity }} barang</span> X Rp {{
                            formatPrice(order.buku.harga) }}</p>
                        </div>
                        <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                          <span><strong>Rp {{ formatPrice(order.quantity * order.buku.harga) }}</strong></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <div class="col-lg-4" v-if="!overlay">
            <div class="card sticky-menu">
              <div class="card-body">
                <div class="py-2">
                  <h3 class="card-title">Rincian Belanja</h3>
                </div>
                <hr class="horizontal dark">
                <p>Ringkasan Pembayaran</p>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Harga
                  </div>
                  <div class="col">
                    <p>: Rp {{ formatPrice(totalPayment) }}</p>
                  </div>
                </div>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total biaya pengiriman
                  </div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(selectedCourier ? selectedCourier.price : 0) }}</p>
                  </div>
                </div>
                <hr class="horizontal dark">
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Bayar
                  </div>
                  <div class="col">
                    <p>: Rp {{ formatPrice(totalPayment + (selectedCourier ? selectedCourier.price : 0)) }}</p>
                  </div>
                </div>
                <button class="btn btn-primary w-100" @click="proceedToCheckout">Lanjut untuk Membayar</button>
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
                <select class="form-select" id="addressSelect" v-model="selectedAddresses" @change="filledAddress">
                  <option v-for="result in searchResults" :key="result.id" :value="result">{{ result.name }}</option>
                </select>
              </div>
              <div v-else><a style="font-size: 12px; color:red;"><i class="fas fa-info-circle"
                    style="color: #ff0000;"></i>&nbsp;Jika Alamat yang dicari tidak muncul, coba ganti kata kunci atau
                  input kode pos!</a></div>
              <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
              <div class="wrap" v-if="selectedAddresses">
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
                    <textarea type="text" class="form-control" id="district"
                      v-model="address.alamat_lengkap"></textarea>
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
              <v-btn color="blue darken-1" text @click="dialog = false">Batal</v-btn>
              <v-btn color="blue darken-1" text @click="saveAddress">Simpan</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
    </div>
  </div>
</template>
<style scoped>
.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: unset;
}

.large-checkbox {
  width: 1.5rem;
  height: 1.5rem;
}

.ring-bayar {
  font-size: 14px;
}

.sticky-menu {
  position: sticky;
  top: 100px;
  padding: 3px;

}

@media (max-width: 576px) {

  .row .col-1,
  .row .col-2,
  .row .col-3,
  .row .col-4,
  .row .col-2 a,
  .row .col-3 a,
  .row .col-4 a,
  .row .col-2 strong,
  .row .col-3 strong,
  .row .col-4 strong {
    font-size: 10px;
  }

  .row img {
    width: 80px;
    margin-right: 10px;
    max-width: 100px;
    max-height: 134px;
    width: 100%;
    height: auto;
    object-fit: cover;
    overflow: hidden;
  }

  .large-checkbox {
    width: 1rem;
    height: 1rem;
  }
}


@media (max-width: 368px) {

  .row .col-1,
  .row .col-2,
  .row .col-3,
  .row .col-4,
  .row .col-2 a,
  .row .col-3 a,
  .row .col-4 a,
  .row .col-2 strong,
  .row .col-3 strong,
  .row .col-4 strong {
    font-size: 8px;
  }

  .row img {
    width: 80px;
    margin-right: 10px;
    max-width: 100px;
    max-height: 134px;
    width: 100%;
    height: auto;
    object-fit: cover;
    overflow: hidden;
  }

  .large-checkbox {
    width: 1rem;
    height: 1rem;
  }
}
</style>