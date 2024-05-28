<script>
// import { useRoute } from "vue-router";
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
      kirim: {
        provinsi: '',
        kota: '',
        kecamatan: '',
        postal_code: '',
        penerima: '',
        no_penerima: ''
      },
      selectedAddressId: "",
      selectedAddress: {},
      shippingRates: {},
    };
  },

  mounted() {
    this.retrieveCart();
    this.fetchUserAddresses();
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
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    async fetchShippingRates() {
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
          width: 15,
          height: 20,
          weight: 200,
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

        this.shippingRates = response.data;
      } catch (error) {
        console.error('Error fetching shipping rates:', error);
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
    updateTotalPayment() {
      this.totalPayment = this.orders.reduce((total, order) => {
        return order.selected ? total + order.totalPrice : total;
      }, 0);
    },
    async proceedToCheckout() {
      const selectedOrders = this.orders.filter(order => order.selected);
      if (selectedOrders.length > 0) {
        try {
          const selectedIds = selectedOrders.map(order => order.id);
          await axios.put(`${BASE_URL}/cart/select`,
            { selected_ids: selectedIds }, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem('access_token')
            }
          });
          this.$router.push({ name: 'Checkout' });
        } catch (error) {
          console.error('Error updating selected items:', error);
        }
      } else {
        alert('Pilih setidaknya satu item untuk melanjutkan ke pembayaran.');
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
    async removeOrder(index) {
      const orderId = this.orders[index].id;
      try {
        await axios.delete(`${BASE_URL}/cart/delete/` + orderId, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.orders.splice(index, 1);
        this.retrieveCart();
      } catch (error) {
        console.error(error);
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
            <div class="row mb-4">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Pilih Alamat <i class="fas fa-plus fa-md mx-4"
                      style="color: #5e72e4; cursor:pointer"></i></h5>
                  <div class="row">
                    <select class="form-select" aria-label="Default select example" v-model="selectedAddressId"
                      @change="fillAddress">
                      <option value="" disabled>Pilih alamat</option>
                      <option v-for="item in alamat" :key="item.selected_address_id" :value="item.selected_address_id">
                        {{ item.name }}
                      </option>
                    </select>
                  </div>
                  <div v-if="selectedAddressId">
                    <hr class="horizontal dark">
                    <div class="row">
                      <div class="row">
                        <div class="col">
                          Nama Penerima
                        </div>
                        <div class="col">
                          : {{ selectedAddress.penerima }}
                        </div>
                        <div class="col">
                          Nomor Penerima
                        </div>
                        <div class="col">
                          : {{ selectedAddress.no_penerima }}
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="row">
                        <div class="col">
                          Provinsi
                        </div>
                        <div class="col">
                          : {{ selectedAddress.provinsi }}
                        </div>
                        <div class="col">
                          Kota
                        </div>
                        <div class="col">
                          : {{ selectedAddress.kota }}
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="row">
                        <div class="col">
                          Kecamatan
                        </div>
                        <div class="col">
                          : {{ selectedAddress.kecamatan }}
                        </div>
                        <div class="col">
                          Kode Pos
                        </div>
                        <div class="col">
                          : {{ selectedAddress.postal_code }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div v-for="(order, index) in orders" :key="index" class="mb-4 card">
                <div class="card-body">
                  <h5 class="card-title">Pesanan {{ index + 1 }}</h5>
                  <div class="row">
                    <div class="col-md-3">
                      <div class="row">
                        <div class="col">
                          <img :src="order.buku.foto" class="img-fluid" alt="Book image">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9">
                      <div class="row">
                        <div class="col">
                          <h6>{{ order.judul }}</h6>
                          <p><span class="mx-2">{{ order.quantity }} barang</span>X Rp {{ formatPrice(order.buku.harga)
                            }}</p>
                        </div>
                        <div class="col">
                          <div class="d-flex align-items-center">
                            <span class="ms-auto"><strong>Rp {{ formatPrice(order.totalPrice) }}</strong></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                <div class="py-2">
                  <h3 class="card-title">Rincian Belanja</h3>
                </div>
                <hr class="horizontal dark">
                <p>Ringkasan Pembayaran</p>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Harga :
                  </div>
                  <div class="col">
                    <p>Rp {{ formatPrice(totalPayment) }}</p>
                  </div>
                </div>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total biaya pengiriman :
                  </div>
                  <div class="col">
                    <p>Rp {{ formatPrice(totalPayment) }}</p>
                  </div>
                </div>
                <button class="btn btn-primary w-100" @click="proceedToCheckout">Lanjut ke Pembayaran</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
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
</style>