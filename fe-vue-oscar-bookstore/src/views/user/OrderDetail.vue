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
      dialogTrack: false,
      orderDetails: '',
      riwayat: [],
      courierTrack: '',
      dialogReview: false,
    };
  },

  async mounted() {
    await this.retrieveDetail();
    if (this.orders && this.orders.bsorder_id) {
      await this.retrieveBsOrder();
      this.overlay = false;
    }
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
      this.store.state.showSidenav = false;
      this.store.state.showFooter = false;
      this.body.classList.remove("bg-gray-100");
    },
    restorePage() {
      this.store.state.hideConfigButton = false;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = false;
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
    formatDateCourier(date) {
      if (!date) return "";
      const options = { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
      return new Date(date).toLocaleDateString('id-ID', options);
    },
    updateTotalPayment() {
      this.totalPayment = this.orders.reduce((total, order) => {
        return order.selected ? total + order.totalPrice : total;
      }, 0);
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
    async payNow() {
      try {
        const response = await axios.get(`${BASE_URL}/order/status`, {
          params: {
            order_id: this.orders.transaction_id,
          },
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        if (response.status === 200) {
          this.retrieveDetail();
        } else {
          window.open(this.orders.link, '_blank');
        }
      } catch (error) {
        console.error("Error checking order status:", error);
        window.open(this.orders.link, '_blank');
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
        this.items = response.data.items.map(item => ({
          ...item,
          rating: 0,
          comment: ''
        }));
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
    async retrieveBsOrder() {
      this.overlay = true;
      const isFinished = this.orders.status === 'finished';
      try {
        const response = await axios.get(`${BASE_URL}/order/bs/` + this.orders.bsorder_id, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.orderDetails = response.data;
        this.riwayat = response.data.courier.history
        this.courierTrack = response.data.courier
      } catch (error) {
        console.error('Error retrieving order details:', error);
        this.$notify({
          type: 'danger',
          title: 'Error',
          text: 'Failed to retrieve order details!',
          color: 'red'
        });
      } finally {
        this.overlay = false;
      }
    },
    async submitReviews() {
      this.loadingRegist = true;
      try {
        for (const item of this.items) {
          const reviewData = {
            user_id: this.orders.user_id,
            buku_id: item.buku.id,
            order_id: this.orders.id,
            rating: item.rating,
            comment: item.comment
          };

          await axios.post(`${BASE_URL}/review/store`, reviewData, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem('access_token')
            }
          });
        }
        this.$notify({
          type: 'success',
          title: 'Notif',
          text: 'Review berhasil dilakukan.'
        });
        this.retrieveDetail();
        this.dialogReview = false;
      } catch (error) {
        console.error(error);
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Failed to submit reviews.'
        });
      } finally {
        this.loadingRegist = false;
      }
    },
    getStatusBadge(status) {
      switch (status) {
        case 'pending':
          return 'badge-danger text-dark';
        case 'paid':
          return 'badge-success';
        case 'process':
          return 'badge-info text-dark';
        case 'packing':
          return 'badge-info text-dark';
        case 'delivery':
          return 'badge-warning text-dark';
        case 'delivered':
          return 'badge-success text-dark';
        case 'finished':
          return 'badge-success text-dark';
        case 'onsite':
          return 'badge-success text-dark';
        case 'expired':
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
        case 'packing':
          return 'Pesanan Dikemas';
        case 'delivery':
          return 'Sedang Dikirim';
        case 'delivered':
          return 'Telah Terkirim';
        case 'finished':
          return 'Pesanan Selesai';
        case 'expired':
          return 'Expired';
        case 'onsite':
          return 'On Site';
        case 'failed':
          return 'Pembayaran Gagal';
        default:
          return 'Tidak Diketahui';
      }
    },
    getStatusCourier(status) {
      switch (status) {
        case 'allocated':
          return 'Allocated';
        case 'picking_up':
          return 'Picking Up';
        case 'picked':
          return 'Picked';
        case 'dropping_off':
          return 'Dropping Off';
        case 'return_in_transit':
          return 'Return In Transit';
        case 'delivered':
          return 'Delivered';
        case 'on_hold':
          return 'On Hold';
        case 'rejected':
          return 'Rejected';
        case 'courier_not_found':
          return 'Courier Not Found';
        case 'returned':
          return 'Returned';
        case 'cancelled':
          return 'Cancelled';
        case 'disposed':
          return 'Disposed';
        default:
          return 'Belum Diproses';
      }
    },
    getNoteDescription(status) {
      switch (status) {
        case 'confirmed':
          return 'Pesanan telah dikonfirmasi. Mencari driver terdekat untuk pick up.';
        case 'allocated':
          return 'Kurir telah dialokasikan, menunggu pick up.';
        case 'picking_up':
          return 'Kurir menuju lokasi pick up.';
        case 'picked':
          return 'Pesanan telah diambil dan siap dikemas.';
        case 'dropping_off':
          return 'Pesanan Anda dalam proses pengiriman.';
        case 'return_in_transit':
          return 'Pesanan dalam perjalanan kembali ke pengirim.';
        case 'delivered':
          return 'Pesanan telah terkirim.';
        case 'on_hold':
          return 'Pengiriman Anda sedang ditahan sementara. Kami akan mengirim item Anda setelah masalah terselesaikan.';
        case 'rejected':
          return 'Pengiriman Anda telah ditolak. Silakan hubungi Biteship untuk informasi lebih lanjut.';
        case 'courier_not_found':
          return 'Pengiriman Anda dibatalkan karena tidak ada kurir yang tersedia saat ini.';
        case 'returned':
          return 'Pesanan berhasil dikembalikan.';
        case 'cancelled':
          return 'Pesanan dibatalkan.';
        case 'disposed':
          return 'Pesanan berhasil dibuang.';
        default:
          return 'Status tidak dikenal.';
      }
    }
  },
  computed: {
    formattedPaymentType() {
      if (this.orders.payment && this.orders.payment.payment_type) {
        return this.orders.payment.payment_type
          .replace(/_/g, ' ')
          .replace(/\b\w/g, char => char.toUpperCase());
      }
      return '';
    }
  }

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
            <div class="row mb-2">
              <div class="card">
                <a class="mt-4" style="font-size: 20px; font-weight: bold"> Alamat </a>
                <div style="font-size: 14px;">
                  <hr class="horizontal dark">
                  <div class="row">
                    <div class="col-md-6 col">
                      <span><strong>Penerima</strong></span>
                      <p>{{ address.penerima }}</p>
                    </div>
                    <div class="col-md-6 col">
                      <span><strong>Nomor Penerima</strong></span>
                      <p>{{ address.no_penerima }}</p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col">
                      <span><strong>Alamat Lengkap</strong></span>
                      <p>{{ address.alamat_lengkap }}</p>
                    </div>
                    <div class="col-md-6 col">
                      <span><strong>Kota</strong></span>
                      <p>{{ address.kota }}</p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col">
                      <span><strong>Provinsi</strong></span>
                      <p>{{ address.provinsi }}</p>
                    </div>
                    <div class="col-md-6 col">
                      <span><strong>Kode Pos</strong></span>
                      <p>{{ address.postal_code }}</p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col">
                      <span><strong>Kecamatan</strong></span>
                      <p>{{ address.kecamatan }}</p>
                    </div>
                    <div class="col-md-6 col">
                      <span><strong>Label</strong></span>
                      <p>{{ address.label }}</p>
                    </div>
                  </div>
                  <div class="row mb-2 mt-4 p-2" v-if="courier && Object.keys(courier).length > 0">
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
                              <a class="d-block d-sm-inline" style="
                        color: black;">{{ courier.courier_name }} {{
        courier.courier_service_name }}</a>
                            </div>
                          </div>
                          <div class="col-4">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Estimasi Pengiriman</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline" style="
                        color: black;">{{ courier.duration }}</a>
                            </div>
                          </div>
                          <div class="col-2">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Tarif</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline" style="
                        color: black;">Rp. {{ formatPrice(courier.price) }}</a>
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
              <div class="mb-2 card" v-for="(item, index) in items" :key="index">
                <router-link :to="'/products/' + item.buku.slug">
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
                              <a class="text-truncate text-bold" style="font-size: 16px; color: black;">{{
        item.buku.judul
      }}</a>
                            </div>
                            <div class="row">
                              <a class="d-inline" style="font-size: 12px; color: black"><span class="mx-2">{{
          item.quantity }}
                                  barang</span> X Rp {{
        formatPrice(item.buku.harga) }}</a>
                            </div>
                            <div class="row" v-if="item.buku.reviews.length > 0">
                              <div style="color: black">
                                <div class="row mt-2">
                                  <div class="col-12 border" style="border-radius: 10px;">
                                    <div class="px-4" v-for="(review, reviewIndex) in item.buku.reviews"
                                      :key="reviewIndex">
                                      <div class="row">
                                        <v-rating class="mt-2" density="compact" readonly v-model="review.rating"
                                          active-color="yellow" color="grey"></v-rating>
                                        <div class="row mt-2">
                                          <p class="text-black">{{ review.comment }}</p>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row" v-else>
                              <div class="col-12 text-center"
                                style="border-radius: 10px; padding: 20px; background-color: #f8f9fa;">
                                <p class="text-dark" style="margin: 0; font-size: 18px;">Belum ada review</p>
                              </div>
                            </div>
                          </div>
                          <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                            <span><strong>Rp {{ formatPrice(item.price) }}</strong></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </router-link>
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
                    <span v-else> - </span>
                  </div>
                </div>
                <a><strong>Tanggal Pemesanan</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span>{{ formatDate(orders.created_at) }}</span>
                  </div>
                </div>
                <hr>
                <a><strong>Metode Pembayaran</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <p><strong>Payment Type </strong><a class="text-uppercase"> {{ orders.payment.bank }}</a> {{
        formattedPaymentType }}</p>
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
                <button v-if="orders.status === 'pending'" class="btn btn-primary w-100" @click="payNow">Bayar</button>
                <button v-if="orders.status == 'pending'" class="btn btn-primary w-100" @click="payNow"><i
                    class="fas fa-info-circle mx-2"></i> Cek Status Bayar</button>
                <button
                  v-if="(orders.status == 'finished' || orders.status == 'onsite') && orders.items.some(item => item.buku.reviews.length === 0)"
                  style="border-color: black;" class="btn btn-outline-light btn-sm text-dark w-100"
                  @click="dialogReview = true">
                  <i class="fas fa-star"></i><a> </a>
                  Berikan Ulasan
                </button>
                <button v-if="orders.status == 'delivered'" style="border-color: black;"
                  class="btn btn-outline-light btn-sm text-dark w-100" @click="dialogReview = true"><i
                    class="fas fa-star"></i><a> </a>
                  Ulas & Konfirmasi </button>
                <button v-if="orders.status != 'pending' && orders.status != 'process' && orders.status != 'onsite'"
                  class="btn btn-primary btn-sm w-100" @click="dialogTrack = true"><i
                    class="fas fa-info-circle mx-2"></i>
                  Lacak Pengiriman </button>
              </div>
            </div>
            <v-dialog v-model="dialogTrack" max-width="788px">
              <v-card style="border-radius: 10px;">
                <v-card-title>
                  <span><a class="text-bold text-dark">Lacak Pengiriman </a></span>
                </v-card-title>
                <v-card-text>
                  <div style="font-family: sans-serif">
                    <div class="wrapper" v-if="courierTrack && courierTrack.company && riwayat.length > 0">
                      <div class="row p-2">
                        <div class="col-sm-12 border" style="border-radius: 10px;">
                          <div>
                            <div class="row align-items-center py-2">
                              <div class="col-3 d-flex align-items-center">
                                <img v-if="courierTrack.company === 'jne'" src="../../assets/img/jne.png" alt="jne"
                                  class="img-fluid" style="width: 50px; margin-right: 20px;" />
                                <img v-if="courierTrack.company === 'sicepat'" src="../../assets/img/sicepat.png"
                                  alt="sicepat" class="img-fluid" style="width: 50px; margin-right: 20px;" />
                              </div>
                              <div class="col-3 text-dark">
                                <div class="row">
                                  <strong class="d-block d-sm-inline">Kurir</strong>
                                </div>
                                <div class="row">
                                  <a class="d-block d-sm-inline" style="
                        color: black;">{{ courierTrack.name }} </a>
                                </div>
                                <div class="row">
                                  <a class="d-block d-sm-inline" style="
                        color: black;">{{ courierTrack.phone }}</a>
                                </div>
                              </div>
                              <div class="col-4">
                                <div class="row">
                                  <strong class="d-block d-sm-inline">Estimasi Pengiriman</strong>
                                </div>
                                <div class="row">
                                  <a class="d-block d-sm-inline" style="
                        color: black;">{{ courier.duration }}</a>
                                </div>
                              </div>
                              <div class="col-2">
                                <div class="row">
                                  <strong class="d-block d-sm-inline">Foto</strong>
                                </div>
                                <div class="row">
                                  <img class="d-block d-sm-inline" style="max-width: 80px"
                                    :src="courierTrack.driver_photo_url">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <a class="text-dark" style="font-size: 16px;">Status Pengiriman</a>
                      <div class="row p-2">
                        <div class="col-12 border py-2 px-2" style="border-radius: 20px;">
                          <div class="row text-end text-mobile" v-for="item in riwayat" :key="item.note">
                            <div class="col-md-6 col-6">
                              {{ formatDateCourier(item.updated_at) }} &#x2022;
                            </div>
                            <div class="col-md-6 col-6">
                              <div class="row text-bold">
                                {{ getStatusCourier(item.status) }}
                              </div>
                              <div class="row text-start">
                                {{ getNoteDescription(item.status) }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="col-sm-12 text-center"
                      style="border-radius: 10px; padding: 20px; background-color: #f8f9fa;">
                      <p class="text-dark" style="margin: 0; font-size: 18px;">Pesananmu sedang di proses</p>
                    </div>
                  </div>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <button type="button" class="btn btn-sm btn-outline-light mx-2 text-dark" style="border-color: black;"
                    @click="dialogTrack = false">Close
                  </button>
                </v-card-actions>
              </v-card>
            </v-dialog>
            <v-dialog v-model="dialogReview" max-width="788px">
              <v-card style="border-radius: 10px;">
                <v-card-title>
                  <span><a class="text-bold text-dark">Review Buku </a></span>
                </v-card-title>
                <v-card-text>
                  <div style="font-family: sans-serif">
                    <div class="wrapper">
                      <div class="row p-2" v-for="(item, index) in items" :key="index">
                        <div class="col-sm-12 border p-2" style="border-radius: 10px;">
                          <div class="row">
                            <div class="col-md-3 col-4">
                              <div class="row">
                                <div class="col">
                                  <img :src="item.buku.foto" class="img-fluid" alt="Book image" style="width: 100%;">
                                </div>
                              </div>
                            </div>
                            <div class="col-md-9 col-8">
                              <div class="row">
                                <div class="col-12">
                                  <div class="row">
                                    <a class="text-truncate text-bold" style="font-size: 16px; color: black;">{{
        item.buku.judul
                                      }}</a>
                                  </div>
                                  <div class="row" style="max-width: 100px;">
                                    <div class="col">
                                      <v-rating density="compact" v-model="item.rating" active-color="blue"
                                        color="orange-lighten-1"></v-rating>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-floating mb-3">
                                      <textarea class="form-control" v-model="item.comment" placeholder="Review Buku"
                                        id="floatingTextarea2" style="height: 100px"></textarea>
                                      <label for="floatingTextarea2">Review Buku</label>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <button type="button" class="btn btn-sm btn-outline-light mx-2 text-dark" style="border-color: black;"
                    @click="dialogReview = false">Close
                  </button>
                  <button type="button" class="btn btn-sm btn-primary" @click="submitReviews"><i
                      class="far fa-star"></i> Confirm Review
                  </button>
                </v-card-actions>
                <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
              </v-card>
            </v-dialog>
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

  .text-mobile {
    font-size: 12px;
  }
}
</style>