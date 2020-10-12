<template>
  <v-content>
    <v-row align="center" class="pa-5 align-center">
      <v-col cols="4">
        <v-alert
          v-model="alert"
          text
          prominent
          type="error"
          icon="mdi-cloud-alert"
          close-text="Close Alert"
          dismissible
        >Ocurrio un error.</v-alert>
      </v-col>
    </v-row>
    <v-row class="pa-5 align-center">
      <v-col>
        <v-btn fab large dark color="blue darken-3" @click="dialogEjemplo = true">
          <v-icon>mdi-plus</v-icon>
        </v-btn>
      </v-col>
      <v-col cols="11">
        <h2 class="font-weight-regular text-center">Mantenimiento de Categoria</h2>
      </v-col>
    </v-row>
    <v-dialog v-model="dialogEjemplo" persistent scrollable max-width="60vw">
      <v-card>
        <v-card-title class="headline indigo darken-4">
          <span v-if="edit" class="headline" style="color:white;">Editar Categoria</span>
          <span v-else-if="ver" class="headline" style="color:white;">Ver Categoria</span>
          <span v-else class="headline" style="color:white;">Nueva Categoria</span>
        </v-card-title>
        <v-card-text>
          <v-form ref="form" v-model="valid" lazy-validation>
            <v-row>
              <v-col cols="6">
                <v-text-field
                  :disabled="ver"
                  v-model="Nombre"
                  :rules="[fieldRules.required]"
                  label="Nombre"
                  prepend-icon="mdi-domain"
                  required
                ></v-text-field>
              </v-col>
               <v-col cols="6">
                <v-text-field
                  :disabled="ver"
                  v-model="Descripcion"
                  :rules="[fieldRules.required]"
                  label="Descripcion"
                  prepend-icon="mdi-domain"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            v-if="!ver"
            color="indigo darken-4"
            text
            @click="(dialogEjemplo = false), limpiar()"
          >Cerrar</v-btn>
          <v-btn
            v-if="!ver"
            :loading="saveLoading"
            :disabled="saveLoading"
            color="indigo darken-4"
            class="ma-2 white--text"
            depressed
            @mousedown="validate"
            @click="executeEventClick"
          >Guardar</v-btn>
          <v-btn
            v-if="ver"
            color="indigo darken-4"
            text
            @click="(dialogEjemplo = false), limpiar()"
          >Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-tabs>
      <v-tab>Tabla 1</v-tab>
      <v-tab>Tabla 2</v-tab>
      <v-tab-item>
        <tables-mostrar
          :headers="headers"
          :options="options"
          :withOptions="true"
          ref="categoriaTable"
          entity="categorias"
        />
      </v-tab-item>
      <v-tab-item>
        <v-row class="justify-center">
          <v-col cols="11">
            <v-card>
              <v-card-title>
                <v-spacer></v-spacer>
                <v-text-field
                  v-model="search"
                  append-icon="mdi-magnify"
                  label="Buscar"
                  single-line
                  hide-details
                ></v-text-field>
              </v-card-title>
              <v-data-table :headers="headers2" :items="categorias" :search="search">
                <template v-slot:[`item.Vigencia`]="{ item }">
                  <v-chip :color="item.Vigencia? 'green': 'red'" dark></v-chip>
                </template>
                <template v-slot:[`item.actions`]="{ item }">
                  <v-icon class="mr-2" @click="verCategoria(item)">mdi-eye</v-icon>
                  <v-icon
                    class="mr-2"
                    @click="cambiarEstadoCategoria(item)"
                  >{{ item.Vigencia? 'mdi-do-not-disturb': 'mdi-check-box-outline' }}</v-icon>
                </template>
              </v-data-table>
            </v-card>
          </v-col>
        </v-row>
      </v-tab-item>
    </v-tabs>
  </v-content>
</template>

<script>
import { del, get, enviarConArchivos, patch } from "../api/api";
// import { logos } from "../api/config";

export default {
  components: {
    TablesMostrar: () => import("../components/TablesMostrar"),
  },
  data() {
    return {
      edit: false,
      ver: false,
      alert: false,
      valid: true,
      saveLoading: false,
      dialogEjemplo: false,
      fieldRules: {
        required: (v) => !!v || "Campo requerido",
        //email: (v) => {
        //  const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        //  return pattern.test(v) || "Correo electrónico incorrecto.";
        //},
        validateCodigoCategoria: (v) => v.length == 6 || "Codigo incorrecto.",
      },
      headers: [
        { text: "N°", value: "Codigo", width: "10%" },
        { text: "Nombre", value: "Nombre", width: "30%" },
        { text: "Descripcion", value: "Descripcion", width: "40%" },
        //{ text: "Correo", value: "Correo", width: "40%" },
        { text: "Acciones", value: "actions", width: "10%" },
      ],
      headers2: [
        { text: "Nombre", value: "Nombre" },
        { text: "Descripcion", value: "Descripcion" },
        { text: "Vigencia", value: "Vigencia" },
        { text: "Acciones", value: "actions" },
      ],
      options2: [
        {
          name: "Ver",
          icon: "mdi-eye",
          function: this.showEditCategoria,
        },
        {
          name: "Cambiar vigencia",
          icon: "mdi-check-box-outline",
          function: this.deleteCategoria,
        },
      ],
      options: [
        {
          name: "Editar",
          icon: "mdi-pencil",
          function: this.showEditCategoria,
        },
        {
          name: "Eliminar",
          icon: "mdi-delete",
          function: this.deleteCategoria,
        },
      ],
      categorias: "",
      search: "",
      CodigoCategoria: "",
      Nombre: "",
      Descripcion: "",
    };
  },
  methods: {
    validate() {
      this.$refs.form.validate();
    },
    limpiar() {
      this.CodigoEmpresa = "";
      this.Nombre = "";
      this.Descripcion = "";
      this.ver = false;
    },
    executeEventClick() {
      if (this.edit === false) {
        this.registerCategoria();
      } else {
        this.editCategoria();
      }
    },
    assembleCategoria() {
      let form = new FormData();
      form.append("CodigoEmpresa",1);
      form.append("Nombre", this.Nombre);
      form.append("Descripcion", this.Descripcion);
      return form;
    },

    registerCategoria() {
      if (this.valid == false) return;
      this.saveLoading = true;
      enviarConArchivos("categorias", this.assembleCategoria()).then(() => {
        this.saveLoading = false;
        this.dialogEjemplo = false;
        this.$refs.categoriaTable.fetchData();
        this.limpiar();
        this.actualizarCategorias();
      });
    },
    editCategoria() {
      if (this.valid == false) return;
      this.saveLoading = true;
      enviarConArchivos("categorias/" + this.editId, this.assembleCategoria())
        .then(() => {
          this.saveLoading = false;
          this.editId = null;
          this.dialogEjemplo = false;
          this.$refs.categoriaTable.fetchData();
          this.limpiar();
        })
        .catch(() => {
          this.alert = true;
        });
    },
    showEditCategoria(categoria) {
      this.edit = true;
      this.editId = categoria.Codigo;
      this.mostrarCategoria(categoria.Codigo).then(() => {
        this.dialogEjemplo = true;
      });
    },
    verCategoria(categoria) {
      this.ver = true;
      this.mostrarCategoria(categoria.Codigo).then(() => {
        this.dialogEjemplo = true;
      });
    },
    deleteCategoria(categoria) {
      del("categoria/" + categoria.Codigo)
        .then(() => {
          this.$refs.categoriaTable.fetchData();
          this.actualizarCategorias();
        })
        .catch(() => {
          this.alert = true;
        });
    },
    cambiarEstadoCategoria(categoria) {
      patch("categoria/" + categoria.Codigo)
        .then(() => {
          this.$refs.categoriaTable.fetchData();
          this.actualizarCategorias();
        })
        .catch(() => {
          this.alert = true;
        });
    },
    actualizarCategorias() {
      get("tablaCategoria").then((data) => {
        this.categorias = data;
      });
    },
    async mostrarCategoria(codigo) {
      const categoria = await get("categorias/" + codigo);
      //this.CodigoEmpresa = 1;
      this.Nombre = categoria.Nombre;
      this.Descripcion = categoria.Descripcion;
    },
  },
  created() {
    this.actualizarCategorias();
  },
};
</script>

<style>
</style>