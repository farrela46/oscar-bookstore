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
        <v-data-table v-model:search="search" :items="getitems">
            <template v-slot:item.no="{ item }">
                <div class="text-start">
                    {{ item.no }}
                </div>
            </template>
            <!-- <template v-slot:header.stock>
                <div class="text-end">Status</div>
            </template> -->

            <!-- <template v-slot:item.stock="{ item }">
                <div class="text-end">
                    <v-chip :color="item.stock ? 'green' : 'red'" :text="item.stock ? 'Active' : 'Inactive'"
                        class="text-uppercase" label size="small"></v-chip>
                </div>
            </template> -->

            <template v-slot:item.actions="{ item }">
                <v-icon size="small" class="me-2" @click="editItem(item)">
                    mdi-pencil
                </v-icon>
                <v-icon size="small" @click="deleteItem(item)">
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
            items: [
                {
                    no: '',
                    nama: 'Nebula GTX 3080',
                    email: '',
                    no_telp: '',
                    actions: '',
                },
                {
                    no: '',
                    name: 'Galaxy RTX 3080',
                    email: '',
                    no_telp: '',
                    actions: '',
                },

            ],
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
    }
}
</script>