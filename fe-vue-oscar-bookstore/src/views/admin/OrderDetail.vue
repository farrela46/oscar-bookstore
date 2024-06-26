<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
import moment from 'moment';

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      orders: [],
      totalPayment: '',
      items: [],
      loadingRegist: false,
      address: {},
      dialog: false,
      selectedCourier: null,
    };
  },

  mounted() {
    this.retrieveDetail();
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
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
    formatDate(data_date) {
      return moment.utc(data_date).format('YYYY-MM-DD')
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
    calculateTotalPrice() {
      return this.items.reduce((total, item) => {
        return total + parseFloat(item.price);
      }, 0);
    },
    calculateTotalPayment() {
      const totalPrice = this.calculateTotalPrice();
      const shippingCost = parseFloat(this.orders.shipping_cost);
      return totalPrice + shippingCost;
    },
    async createOrder() {
      const orderData = {
        // Data kontak dan alamat pengirim
        origin_contact_name: "Farrel",
        origin_contact_phone: "085179684772",
        origin_address: "Jl. Ahmad Yani No.85, Tepus, Sukorejo, Kec. Ngasem, Kabupaten Kediri, Jawa Timur 64129",
        origin_note: "Toko Buku Oscar",
        origin_postal_code: 64129,
        origin_area_id: "IDNP11IDNC172IDND1288IDZ64129",
        // Data kontak dan alamat penerima
        destination_contact_name: this.address.penerima,
        destination_contact_phone: this.address.no_penerima,
        destination_address: this.address.alamat_lengkap,
        destination_postal_code: parseInt(this.address.postal_code),
        destination_area_id: this.address.selected_address_id,
        // Detail kurir
        courier_company: this.courier.company,
        courier_type: this.courier.courier_service_code,
        delivery_type: "now",
        metadata: {}, // Metadata jika diperlukan
        items: this.items.map(item => ({
          name: item.buku.judul,
          description: item.buku.desc,
          value: parseInt(item.price),
          quantity: item.quantity,
          length: 30,
          width: 2,
          height: 20,
          weight: 150,
        })),
        // Tambahkan order_id dan item_ids untuk backend
        order_id: this.orders.id,
        item_ids: this.items.map(item => ({ // Mengumpulkan item_id dan quantity
          item_id: item.buku.id,
          quantity: item.quantity
        }))
      };

      try {
        const response = await axios.post(`${BASE_URL}/order/create`, orderData, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        console.log('Order created:', response.data);
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Order successfully created!',
          color: 'green'
        });
        this.retrieveDetail();
      } catch (error) {
        console.error('Error creating order:', error);
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to create order!',
          color: 'red'
        });
      }
    },

    async retrieveDetail() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/order/` + this.$route.params.id, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.orders = response.data
        this.address = response.data.address;
        this.items = response.data.items;
        this.courier = JSON.parse(response.data.courier_details);
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
    getStatusBadge(status) {
      switch (status) {
        case 'pending':
          return 'badge-warning text-dark';
        case 'paid':
          return 'badge-success';
        case 'process':
          return 'badge-info';
        case 'delivery':
          return 'badge-warning text-dark';
        case 'expired':
          return 'badge-danger';
        case 'failed':
          return 'badge-danger';
        default:
          return 'badge-secondary';
      }
    },
    getStatusText(status) {
      switch (status) {
        case 'pending':
          return 'Menunggu Pembayaran';
        case 'process':
          return 'Pesanan Diproses';
        case 'delivery':
          return 'Dalam Pengiriman';
        case 'paid':
          return 'Pembayaran Berhasil';
        case 'expired':
          return 'Expired';
        case 'failed':
          return 'Pembayaran Gagal';
        default:
          return 'Tidak Diketahui';
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
        <div class="row" v-if="courier">
          <div class="col-lg-8">
            <div class="row mb-4">
              <div class="card">
                <a class="mt-4" style="font-size: 20px; font-weight: bold; color:black"> Alamat </a>
                <div style="font-size: 14px;">
                  <hr class="horizontal dark">
                  <div class="row">
                    <div class="row">
                      <div class="col">
                        <a>Nama Penerima</a>
                      </div>
                      <div class="col">
                        <a> : {{ address.penerima }} </a>
                      </div>
                      <div class="col">
                        <a>Nomor Penerima </a>
                      </div>
                      <div class="col">
                        <a>: {{ address.no_penerima }}</a>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="row">
                      <div class="col">
                        <a> Provinsi </a>
                      </div>
                      <div class="col">
                        <a> : {{ address.provinsi }} </a>
                      </div>
                      <div class="col">
                        Kota
                      </div>
                      <div class="col">
                        <a> : {{ address.kota }} </a>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="row">
                      <div class="col">
                        <a> Kecamatan </a>
                      </div>
                      <div class="col">
                        <a> : {{ address.kecamatan }} </a>
                      </div>
                      <div class="col">
                        <a> Kode Pos </a>
                      </div>
                      <div class="col">
                        <a> : {{ address.postal_code }} </a>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-2 mt-4 p-2">
                    <div class="col-sm-12 border" style="border-radius: 10px;">
                      <div class="p-2">
                        <div class="row align-items-center py-2">
                          <div class="col-3 d-flex align-items-center">
                            <img v-if="courier.company === 'jne'" src="../../assets/img/jne.png" alt="jne"
                              class="img-fluid" style="width: 50px; margin-right: 20px;" />
                            <img v-if="courier.company === 'sicepat'" src="../../assets/img/sicepat.png" alt="sicepat"
                              class="img-fluid" style="width: 50px; margin-right: 20px;" />
                          </div>
                          <div class="col-3">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Jenis Layanan</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">{{ courier.courier_name }} {{
        courier.courier_service_name }}</a>
                            </div>
                          </div>
                          <div class="col-4">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Estimasi Pengiriman</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">{{ courier.duration }}</a>
                            </div>
                          </div>
                          <div class="col-2">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Tarif</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">Rp. {{ formatPrice(courier.price) }}</a>
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
              <div class="mb-4 card" v-for="(item, index) in items" :key="index">
                <div class="card-body">
                  <h6 class="card-title">Pesanan {{ index + 1 }}</h6>
                  <div class="row">
                    <div class="col-md-3 col-4">
                      <div class="row">
                        <div class="col">
                          <img :src="item.buku.foto" class="img-fluid" alt="Book image" style="max-width: 100px;">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9 col-8">
                      <div class="row">
                        <div class="col-12">
                          <div class="row">
                            <a class="text-truncate text-bold" style="font-size: 16px; color: black;">{{ item.buku.judul
                              }}</a>
                          </div>
                          <div class="row">
                            <a class="d-inline" style="font-size: 12px;"><span class="mx-2">{{ item.quantity }}
                                barang</span> X Rp {{
        formatPrice(item.buku.harga) }}</a>
                          </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                          <span><strong>Rp {{ formatPrice(item.price) }}</strong></span>
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
                  <h4 class="card-title">Order Detail</h4>
                </div>
                <hr>
                <a><strong>Status Transaksi</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span :class="['badge', getStatusBadge(orders.status)]">{{ getStatusText(orders.status) }}</span>
                  </div>
                </div>
                <a><strong>No. Transaksi</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span>#{{ orders.transaction_id }}</span>
                  </div>
                </div>
                <a><strong>Nomor Resi</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span v-if="orders.waybill_id">{{ orders.waybill_id }}</span>
                    <span else>-</span>
                  </div>
                </div>
                <a><strong>Tanggal Pemesanan</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span>{{ formatDate(orders.created_at) }}</span>
                  </div>
                </div>
                <hr>
                <p>Ringkasan Pembayaran</p>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Harga
                  </div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(calculateTotalPrice()) }}</p>
                  </div>
                </div>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total biaya pengiriman
                  </div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(orders.shipping_cost) }}</p>
                  </div>
                </div>
                <hr>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Bayar
                  </div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(calculateTotalPayment()) }}</p>
                  </div>
                </div>
                <button v-if="orders.status === 'process'" class="btn btn-primary btn-sm w-100"
                  @click="createOrder">Proses
                  Pesanan</button>
                <button v-if="orders.status == 'pending'" class="btn btn-primary w-100" @click="payNow"><i
                    class="fas fa-info-circle mx-2"></i> Cek Status Bayar</button>
              </div>
            </div>
          </div>
        </div>
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
    width: 60px;
    margin-right: 10px;
  }
}
</style>