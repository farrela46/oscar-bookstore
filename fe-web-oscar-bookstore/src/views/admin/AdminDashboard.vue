<template>
    <Navbar>
        <div>
            <div class="container-fluid px-4 py-4">
                <div class="card-list">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="cardstat blue">
                                <div class="title">all projects</div>
                                <i class="zmdi zmdi-upload"></i>
                                <div cl ass="value">89</div>
                                <div class="stat"><b>13</b>% increase</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="cardstat green">
                                <div class="title">team members</div>
                                <i class="zmdi zmdi-upload"></i>
                                <div class="value">5,990</div>
                                <div class="stat"><b>4</b>% increase</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="cardstat orange">
                                <div class="title">total budget</div>
                                <i class="zmdi zmdi-download"></i>
                                <div class="value">$80,990</div>
                                <div class="stat"><b>13</b>% decrease</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="cardstat red">
                                <div class="title">new customers</div>
                                <i class="zmdi zmdi-download"></i>
                                <div class="value">3</div>
                                <div class="stat"><b>13</b>% decrease</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-white">
                                <h5 class="card-title">Grafik Penjualan (Bulan)</h5>
                            </div>
                            <div class="card-body">
                                <div class="wrapper">
                                    <Bar :data="chartData" :style="myStyles" :options="options" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-white">
                                <h5 class="card-title">Grafik Pemasukan (Bulan)</h5>
                            </div>
                            <div class="card-body">
                                <div class="wrapper">

                                    <Line :data="lineData" :style="myStyles"
                                        :options="{ responsive: true, maintainAspectRatio: false }" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Navbar>
</template>
<script>
import Navbar from '@/components/AdminNavbar.vue';
import { Bar } from 'vue-chartjs'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement } from 'chart.js'
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement)

export default {
    name: 'AdminDashboard',
    components: {
        Navbar,
        Bar,
        Line
    },
    data() {
        return {
            chartData: {
                labels: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                datasets: [
                    {
                        label: 'Data One',
                        backgroundColor: '#f87979',
                        data: [40, 20, 12, 21, 22, 23, 31, 42, 53, 43, 76, 82],
                    }
                ],
                options: {
                    scales: {
                        yAxes: {
                            beginAtZero: true,
                            min: 0,
                            max: 100,
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    },
                },
            },
            lineData: {
                labels: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                datasets: [
                    {
                        label: 'Data One',
                        backgroundColor: '#f87979',
                        data: [40, 20, 12, 21, 22, 23, 31, 42, 53, 43, 76, 82],
                    }
                ]
            },
        };

    },
    mounted() {
    },
    methods: {

    },
    computed: {
        myStyles() {
            return {
                height: `${100}vh`,
                position: 'relative'
            }
        }
    }
}
</script>
<style scoped>
.wrapper {
    height: 400px !important;
}

.dashboard-admin {
    min-height: 100vh;

    background: url("../../../src/assets/LandingPage/Background.png");
    background-position: center;
    background-size: cover;
}

.card-list {
    /* @include clear(); */
    width: 100%;
}

.cardstat {
    border-radius: 8px;
    color: white;
    padding: 10px;
    position: relative;

    .zmdi {
        color: white;
        font-size: 28px;
        opacity: 0.3;
        position: absolute;
        right: 13px;
        top: 13px;
    }

    .stat {
        border-top: 1px solid rgba(255, 255, 255, 0.3);
        font-size: 8px;
        margin-top: 25px;
        padding: 10px 10px 0;
        text-transform: uppercase;
    }

    .title {
        display: inline-block;
        font-size: 8px;
        padding: 10px 10px 0;
        text-transform: uppercase;
    }

    .value {
        font-size: 28px;
        padding: 0 10px;
    }

    &.blue {
        background-color: #2298F1;
    }

    &.green {
        background-color: #66B92E;
    }

    &.orange {
        background-color: #DA932C;
    }

    &.red {
        background-color: #D65B4A;
    }
}
</style>
