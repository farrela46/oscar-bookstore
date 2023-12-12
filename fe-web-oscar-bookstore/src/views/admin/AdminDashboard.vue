<template>
    <div class="dashboard-admin">
        <Navbar />
        <div class="container-fluid px-4 py-4">
            <div class="row">
                <div class="col-6">
                    <div class="card shadow">
                        <!-- <Bar :data="chartData" /> -->
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow">
                        <!-- <line-chart :chart-data="datacollection"></line-chart> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Navbar from '@/components/AdminNavbar.vue';
// import { Bar } from 'vue-chartjs'
// import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
// ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
    name: 'AdminDashboard',
    components: {
        Navbar,
        // Bar,
    },
    data() {
        return {
            chartData: {
                labels: ['January', 'February', 'March'],
                datasets: [
                    {
                        label: 'Data One',
                        backgroundColor: '#f87979',
                        data: [40, 20, 12]
                    }
                ]
            },
            datacollection: {
                labels: ["week 1", "week 2", "week 3", "week 4", "week 5", "week 6", "week 7", "week 8", "week 9", "week 10"],
                datasets: [
                    {
                        data: [86, 114, 106, 106, 107, 111, 133, 221, 783, 2478],
                        label: "Africa",
                        borderColor: "#3e95cd",
                        fill: false
                    },
                    {
                        data: [282, 350, 411, 502, 635, 809, 947, 1402, 3700, 5267],
                        label: "Asia",
                        borderColor: "#8e5ea2",
                        fill: false
                    },
                    {
                        data: [168, 170, 178, 190, 203, 276, 408, 547, 675, 734],
                        label: "Europe",
                        borderColor: "#3cba9f",
                        fill: false
                    },
                    {
                        data: [40, 20, 10, 16, 24, 38, 74, 167, 508, 784],
                        label: "Latin America",
                        borderColor: "#e8c3b9",
                        fill: false
                    },
                    {
                        data: [6, 3, 2, 2, 7, 26, 82, 172, 312, 433],
                        label: "North America",
                        borderColor: "#c45850",
                        fill: false
                    }
                ]
            }
    };

},
mounted() {
},
methods: {

}
}
</script>
<style scoped>
.dashboard-admin {
    min-height: 100vh;

    background: url("../../../src/assets/LandingPage/Background.png");
    background-position: center;
    background-size: cover;
}
</style>
