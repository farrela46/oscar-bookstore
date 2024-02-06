<template>
    <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
            <v-icon icon="mdi-account-supervisor"></v-icon> &nbsp;
            Daftar Buku

            <v-spacer></v-spacer>

            <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" density="compact" label="Search" single-line
                flat hide-details variant="solo-filled"></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table v-model:search="search" :items="items" item-key="item.id">
            <template v-slot:item.id="{ item }">
                <div v-if="false">{{ item.id }}</div>
            </template>
            <template v-slot:header.id>
            </template>
            <template v-slot:item.no="{ item }">
                <div class="text-start">
                    {{ item.no }}
                </div>
            </template>
            <template v-slot:item.foto="{ item }">
                <v-card class="my-2" elevation="2" rounded>
                    <v-img :src="item.foto" height="64" cover></v-img>
                </v-card>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-icon size="large" class="me-2" @click="editBuku(item)">
                    mdi-pencil
                </v-icon>
                <v-icon size="large" @click="deleteBuku(item)">
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
            fotoUrl: '',
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
        this.retrieveBuku();
    },
    methods: {
        // async retrieveUser() {
        //     try {
        //         const response = await axios.get(BASE_URL + '/buku/get', {
        //             headers: {
        //                 Authorization: "Bearer " + localStorage.getItem('access_token')
        //             }
        //         });
        //         this.items = response.data.map((item, index) => ({
        //             id: item.id,
        //             no: index + 1,
        //             ISBN: item.no_isbn,
        //             Judul: item.judul,
        //             desc: item.desc,
        //             pengarang: item.pengarang,
        //             penerbit: item.penerbit,
        //             Tahun_Terbit: item.tahun_terbit,
        //             Harga : item.harga,
        //             stok : item.stok,
        //             actions: '',
        //         }));
        //     } catch (error) {
        //         console.error(error);
        //     }
        // },
        async retrieveBuku() {
            try {
                const response = await axios.get(BASE_URL + '/buku/get', {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem('access_token')
                    }
                });

                this.items = response.data.map((item, index) => ({
                    id: item.id,
                    no: index + 1,
                    "NO ISBN": item.no_isbn,
                    Judul: item.judul,
                    "Tahun Terbit": item.tahun_terbit,
                    Harga: item.harga,
                    stok: item.stok,
                    foto: item.foto,
                    actions: '',
                }));

                if (response.data.length > 0) {
                    this.fotoUrl = response.data[0].foto;
                }
            } catch (error) {
                console.error(error);
            }
        },
        async deleteBuku(item) {
            try {
                const id = item.id
                const response = await axios.delete(BASE_URL + '/buku/delete/' + id, {
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
                this.retrieveBuku();
            } catch (error) {
                console.error(error);
            }
        },
        editBuku(item) {
            const id = item.id
            // console.log(id)
            this.$router.push({ path: `/admin/daftarbuku/editbuku/` + id })
        },
    }
}
</script>