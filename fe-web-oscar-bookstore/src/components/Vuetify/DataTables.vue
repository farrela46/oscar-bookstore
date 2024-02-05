<template>
    <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
            <v-icon icon="mdi-account-supervisor"></v-icon> &nbsp;
            Daftar Customer

            <v-spacer></v-spacer>

            <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" density="compact" label="Search" single-line
                flat hide-details variant="solo-filled"></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table v-model:search="search" :items="getitems" item-key="id">
            <template v-slot:item.id="{ item }">
                <div class="text-start bold">
                    {{ item.id }}
                </div>
            </template>
            <template v-slot:item.no="{ item }">
                <div class="text-start">
                    {{ item.no }}
                </div>
            </template>
            <template v-slot:item.actions="{ item }">
                <!-- <v-icon size="large" class="me-2" data-bs-toggle="modal" data-bs-target="#editModal">
                    mdi-pencil
                </v-icon> -->
                <v-icon size="large" @click="deleteUser(item)">
                    mdi-delete
                </v-icon>
            </template>
            <template v-slot:no-data>
                <v-btn color="primary" @click="initialize">
                    Reset
                </v-btn>
            </template>
        </v-data-table>
    </v-card>
</template>
<script>
import axios from 'axios';
const BASE_URL = import.meta.env.VITE_BASE_URL_API;

export default {
    data() {
        return {
            search: '',
            items: [],
            items_edit: [],
           
            showModal: false,
        }
    },
    computed: {
        getitems() {
            // Add a sequential number to each item
            return this.items.map((item, index) => {
                return { ...item, no: index + 1 };
            });
        },
    },
    mounted() {
        this.retrieveUser();

    },
    methods: {
        async retrieveUser() {
            try {
                const response = await axios.get(BASE_URL + '/getUser', {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem('access_token')
                    }
                });
                this.items = response.data.map((item, index) => ({
                    id: item.id,
                    no: index + 1,
                    nama: item.name,
                    email: item.email,
                    no_telp: item.notelp,
                    actions: '',
                }));
            } catch (error) {
                console.error(error);
            }
        },
        async deleteUser(item) {
            try {
                const id = item.id
                const response = await axios.delete(BASE_URL + '/deleteUser/' + id, {
                    headers: {
                        Authorization: 'Bearer ' + localStorage.getItem('access_token'),
                    },
                });
                console.log(item.no)
                this.$notify({
                    type: 'success',
                    title: 'Success',
                    text: 'Akun berhasil dihapus',
                    color: 'green'
                });
                this.retrieveUser();
            } catch (error) {
                console.error(error);
            }
        },
        showModal(id_user) {
            $('#editModal').modal('show');
            // let obj = this.items.find(o => o.item.id === id_user);
            // this.items_edit = obj;
            // this.showModal = true;
        },

        // Method to hide the modal
        hideTheModal() {
            this.showModal = false;
        },

    }
}
</script>