<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
import * as bootstrap from 'bootstrap';
import ArgonInput from "@/components/ArgonInput.vue";

export default {
  components: {
    Navbar,
    ArgonInput
  },
  data() {
    return {
      overlay: false,
      orders: [],
      // totalPayment: '',
      alamat: [],
      loadingRegist: false,
      loadingKurir: false,
      searchQuery: '',
      searchResults: [],
      selectedAddresses: null,
      address: {
        provinsi: 'Jawa Timur',
        kota: 'Kediri',
        kecamatan: 'Kediri Kota',
        postal_code: '64129',
        penerima: 'Toko Buku Oscar',
        no_penerima: '083115550808',
        label: 'Toko',
        alamat_lengkap: 'Toko Buku Oscar'
      },
      kirim: {
        provinsi: 'Jawa Timur',
        kota: '',
        kecamatan: '',
        postal_code: '',
        penerima: '',
        no_penerima: ''
      },
      selectedAddressId: "",
      selectedAddress: {},
      shippingRates: [],
      dialog: false,
      selectedCourier: null,
      totalPay: 0,
      customerEmail: '',
      orderInvoice: {
        user: {},
        order: {
          items: []
        }
      },
      dialogCashier: false
    };
  },

  mounted() {
    this.retrieveCart();
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  computed: {
    totalPayment() {
      return this.orders.reduce((sum, order) => sum + order.totalPrice, 0);
    }
  },
  watch: {
    selectedAddressId() {
      this.fillAddress();
      this.fetchShippingRates();
    },
    totalPayment: {
      handler(newValue) {
        this.totalPay = newValue;
      },
      immediate: true
    }
  },
  methods: {
    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
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
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },

    selectCourier(rate) {
      this.selectedCourier = rate;
    },
    closeModalDelete() {
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.hide();
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
    async removeOrder(id) {
      try {
        await axios.delete(`${BASE_URL}/cart/delete/` + id, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.retrieveCart();
      } catch (error) {
        console.error(error);
      }
    },
    incrementQuantity(index) {
      const order = this.orders[index];
      const newQuantity = order.quantity + 1;
      this.updateQuantity(index, order.id, newQuantity);
    },
    decrementQuantity(index) {
      const order = this.orders[index];
      const newQuantity = order.quantity - 1;
      if (newQuantity > 0) {
        this.updateQuantity(index, order.id, newQuantity);
      }
    },
    openDeleteConfirmation(index) {
      const orderId = this.orders[index].id;
      this.selectedProductId = orderId
      let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmationModal'))
      modal.show();
    },
    confirmDelete() {
      if (this.selectedProductId) {
        this.removeOrder(this.selectedProductId);
        this.closeModalDelete();
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
    async createOfflineOrder() {
      this.overlay = true;
      try {
        const response = await axios.post(`${BASE_URL}/order/onsite`, {
          email: this.customerEmail,
          amount: this.totalPay,
          items: this.orders.map(order => ({
            buku_id: order.buku_id,
            quantity: order.quantity,
            totalPrice: order.totalPrice,
          })),
        }, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'application/json',
          },
        });

        console.log(response);
        this.orderInvoice = response.data;
      } catch (error) {
        console.error('Error creating offline order:', error);
      } finally {
        this.overlay = false;
        this.dialogCashier = true;
      }
    },
    pesananSelesai() {
      this.dialogCashier = false;
      this.$router.push('/admin/cart');
    },
    async proceedToCheckout() {
      this.overlay = true
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
          first_name: 'Farrel',
          last_name: '',
          email: 'farrel@example.com',
          phone: '815559800895',
        }, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'application/json',
          },
        });

        const { paymentUrl } = response.data;
        window.open(paymentUrl, '_blank');
        setTimeout(() => {
          this.$router.push('/orders')
        }, 1000); // Adjust the delay as needed
      } catch (error) {
        console.error('Error proceeding to checkout:', error);
      } finally {
        this.overlay = false
      }
    },
    async retrieveCart() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/cart/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.orders = response.data.data
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
        <div class="row">
          <div class="col-lg-8">
            <div class="row mb-2">
              <div class="card">
                <div class="card-body">
                  <div>
                    <hr class="horizontal dark">
                    <div class="row">
                      <div class="col-md-6 col">
                        <span><strong>Nama Penerima</strong>
                          <p>{{ address.penerima }}</p>
                        </span>
                      </div>
                      <div class="col-md-6 col">
                        <span><strong>Nomor Penerima</strong>
                          <p>{{ address.no_penerima }}</p>
                        </span>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 col">
                        <span><strong>Provinsi</strong>
                          <p>{{ address.provinsi }}</p>
                        </span>
                      </div>
                      <div class="col-md-6 col">
                        <span><strong>Kota</strong>
                          <p>{{ address.kota }}</p>
                        </span>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 col">
                        <span><strong>Kecamatan</strong>
                          <p>{{ address.kecamatan }}</p>
                        </span>
                      </div>
                      <div class="col-md-6 col">
                        <span><strong>Kode Pos</strong>
                          <p>{{ address.postal_code }}</p>
                        </span>
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
                          <img :src="order.foto" class="img-fluid" alt="Book image">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9 col-8">
                      <div class="row">
                        <div class="col-md-8">
                          <h5 class="text-truncate">{{ order.judul }}</h5>
                          <p class="d-inline"><span class="mx-2">{{ order.quantity }} barang</span> X Rp {{
        formatPrice(order.harga) }}</p>
                        </div>
                        <div class="col-md-4">
                          <div class="d-flex align-items-center">
                            <i class="fas fa-minus-circle" style="cursor: pointer;"
                              @click="decrementQuantity(index)"></i>
                            <span class="mx-2">{{ order.quantity }}</span>
                            <i class="fas fa-plus-circle" style="cursor: pointer;"
                              @click="incrementQuantity(index)"></i>
                            <button class="btn btn-link text-danger ms-3" @click="openDeleteConfirmation(index)">
                              <i class="bi bi-trash"></i> Hapus
                            </button>
                          </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                          <span><strong>Rp {{ formatPrice(order.quantity * order.harga) }}</strong></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
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
                <argon-input type="text" placeholder="Email Pelanggan" v-model="customerEmail" />
                <hr class="horizontal dark">
                <button class="btn btn-primary w-100" @click="createOfflineOrder">Lanjut untuk Membayar</button>
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
                Are you sure you want to remove this product?
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" @click="confirmDelete">Remove</button>
              </div>
            </div>
          </div>
        </div>
        <v-dialog v-model="dialogCashier" persistent max-width="788px">
          <v-card>
            <v-card-title>
              <span class="headline">Order Created</span>
            </v-card-title>
            <v-card-text>
              <div class="mb-3 row">
                <div class="col-sm-2">
                  <label for="status" class="col-form-label">Email User</label>
                </div>
                <div class="col-sm-10" style="padding-right: 20px">
                  <input type="text" class="form-control" v-model="orderInvoice.user.email" disabled>
                </div>
              </div>
              <div class="row">
                <div class="mb-2 border" v-for="(item, index) in orderInvoice.order.items" :key="index"
                  style="border-radius: 10px">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-3 col-4">
                        <div class="row">
                          <div class="col-9">
                            <img :src="item.foto" class="img-fluid" alt="Book image" style="max-width: 100px;">
                          </div>
                        </div>
                      </div>
                      <div class="col-md-9 col-8">
                        <div class="row">
                          <div class="col-9">
                            <div class="row">
                              <a class="text-truncate text-bold" style="font-size: 16px; color: black;">{{ item.judul
                                }}</a>
                              <p><strong>Rp {{ formatPrice(item.totalPrice) }}</strong> </p>
                              <p class="d-inline"><span class="mx-2">{{ item.quantity }} barang</span></p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <h4>Total Transaksi Rp. {{ formatPrice(orderInvoice.order.total_payment) }}</h4>
              </div>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="pesananSelesai">Selesai</v-btn>
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
  }
}
</style>