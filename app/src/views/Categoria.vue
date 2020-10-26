<template>
 <v-container style="padding: 0px ">
    <v-row class="pa-5 align-center">
      <v-col>
        <v-btn
          fab
          large
          dark
          color="blue"
          @click="dialogEjemplo = true"
        >
          <v-icon>mdi-plus</v-icon>
        </v-btn>
      </v-col>
      <v-col cols="11">
        <h2 class="font-weight-bold text-center">Mantenimiento de Categoria</h2>
      </v-col>
    </v-row>
    <v-dialog v-model="dialogEjemplo" persistent scrollable max-width="60vw">
      <v-card>
        <v-card-title class="headline indigo darken-4">
          <span v-if="edit" class="headline" style="color:white;">Editar Categoria</span>
          <span v-else class="headline" style="color:white;">Nueva Categoria</span>
        </v-card-title>
        <v-card-text>
          <v-form ref="form" v-model="valid" lazy-validation>
            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="Nombre"

                  label="Nombre *"
                  prepend-icon="mdi-domain"
                  required
                ></v-text-field>
              </v-col>
               <v-col cols="6">
                <v-text-field
                  v-model="Descripcion"

                  label="Descripcion *"
                  prepend-icon="mdi-domain"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
          <span class="red--text">(*) Campos Obligatorios</span>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="indigo darken-4"
            text
            @click="(dialogEjemplo = false), limpiar()"
            >Cancelar
          </v-btn>
            <v-btn
            
            :loading="saveLoading"
            :disabled="saveLoading"
            color="indigo darken-4"
            class="ma-2 white--text"
            depressed
            @mousedown="validate"
            @click="executeEventClick"
            >Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-card>
        <v-row >
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
            <v-col cols="12" class="pt-0">
                <v-data-table
                    :loading="tableLoading"
                    :headers="headers"
                    :items="categorias"
                    :search="search">
                    <template v-slot:[`item`]="{ item }">
                        <tr v-bind:class="item.Vigencia?'activo':'desactivo'">
                        <td>{{ categorias.indexOf(item) + 1 }}</td>
                        <td>{{ item.Nombre }}</td>
                        <td>{{ item.Descripcion }}</td>
                        <td>
                            <v-tooltip bottom> 
                                <template v-slot:activator="{ on, attrs }">    
                                    <v-icon  class="mr-2" 
                                    v-bind="attrs"
                                    v-on="on"
                                    color="blue-grey"
                                    @click="showEditCategoria(item)">mdi-border-color</v-icon>
                                    </template >
                                <span>Editar</span>
                            </v-tooltip>

                            <v-tooltip bottom> 
                                <template v-slot:activator="{ on, attrs }">
                                <v-icon 
                                v-bind="attrs"
                                    v-on="on"
                                 class="mr-2" 
                                 :color="item.Vigencia ? 'red lighten-1' : 'green'"  @click="cambiarEstadoCategoria(item)">
                                {{item.Vigencia? "mdi-close-circle-outline": "mdi-checkbox-marked-circle-outline"}}
                                </v-icon>
                            </template >
                        <span>{{ item.Vigencia ? "Dar de baja" : "Dar de alta" }}</span>
                            </v-tooltip>
                            
                        </td>
                        </tr>
                    </template>
                </v-data-table>
            </v-col>
        </v-row>
    </v-card>
 </v-container>
</template>

<script>
import { get, post, put, patch } from "../api/api";
// import { logos } from "../api/config";
import Swal from "sweetalert2";
export default {
  components: {
    //TablesMostrar: () => import("../components/TablesMostrar"),
  },
  data() {
    return {
      edit: false,
      //ver: false,
      alert: false,
      valid: true,
      saveLoading: false,
      dialogEjemplo: false,
      tableLoading: true,
      fieldRules: {
        required: (v) => !!v || "Campo requerido",
        //email: (v) => {
        //  const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        //  return pattern.test(v) || "Correo electrónico incorrecto.";
        //},
        validateCodigoCategoria: (v) => v.length == 6 || "Codigo incorrecto.",
      },
      headers: [
        {
          text: "N°",
          sortable: false,
          value: "index",
          width: "10%",
          class:'light blue darken-4 white--text'
        },
        { text: "Nombre", align: "start", value: "Nombre", width: "50%", class:'light blue darken-4 white--text' },
        { text: "Descripcion", value: "Descripcion", width: "30%", class:'light blue darken-4 white--text' },
        { text: "Acciones", value: "actions", width: "20%", class:'light blue darken-4 white--text' },
      ],
      categorias: [],
      search: "",
      CodigoEmpresa: 0,
      Nombre: "",
      Descripcion: "",
      valor: ""
    };
  },
  methods: {
    fetchData() {
      this.tableLoading = true;
      get("categorias").then((data) => {
        this.tableLoading = false;
        this.categorias = data;
      });
    },
    validate() {
      this.$refs.form.validate();
    },
    limpiar() {
      this.CodigoEmpresa = 1;
      this.Nombre = "";
      this.Descripcion = "";
    },
    executeEventClick() {
      if (this.edit === false) {
        this.registerCategoria();
      } else {
        this.editCategoria();
      }
    },
    assembleCategoria() {
       return {
        CodigoEmpresa: 1,
        Nombre: this.Nombre,
        Descripcion: this.Descripcion        
      }
    },

    registerCategoria() {
      
      if (this.valid == false) return;
      this.saveLoading = true;
      
      post("categorias", this.assembleCategoria()).then(() => {
        this.saveLoading = false;
        this.dialogEjemplo = false;
        this.fetchData();
        this.limpiar();
        Swal.fire({
          title: "Sistema",
          text: "Categoria registrada correctamente.",
          icon: "success",
          confirmButtonText: "OK",
        });
        this.actualizarCategorias();
      });
    },

    editCategoria() {
      if (this.valid == false) return;
      this.saveLoading = true;
      put("categorias/" + this.editId, this.assembleCategoria())
        .then(() => {
          this.saveLoading = false;
          this.editId = null;
          this.dialogEjemplo = false;
          this.edit = false;
          this.fetchData();
          this.limpiar();
          Swal.fire({
          title: "Sistema",
          text: "Categoria actualizada correctamente.",
          icon: "success",
          confirmButtonText: "OK",
        });
          this.actualizarEmpresas();
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

    cambiarEstadoCategoria(categoria) {
      patch("categoria/" + categoria.Codigo)
        .then((data) => {
          if(data.Vigencia == 1){
          Swal.fire({
            position: "top-center",
            title: "Sistema",
            text: "Categoria habilitada con éxito",
            icon: "success",
            confirmButtonText: "Ok",
            timer: 2500,
          });
        }else{
          Swal.fire({
            position: "top-center",
            title: "Sistema",
            text: "Categoria dada de baja con éxito ",
            icon: "success",
            confirmButtonText: "Ok",
            timer: 2500,
          });
        }
          this.fetchData();
          this.actualizarCategorias();
        })
        .catch(() => {
          this.alert = true;
        });
    },
    actualizarCategorias() {
      get("categorias").then((data) => {
        this.categorias = data;
      });
    },
    async mostrarCategoria(codigo) {
      const categoria = await get("categorias/" + codigo);
      this.CodigoEmpresa = 1;
      this.Nombre = categoria.Nombre;
      this.Descripcion = categoria.Descripcion;
    },

  },
  created() {
    this.actualizarCategorias();
    this.fetchData();
  },
};
</script>

<style>
 tr.desactivo{
    color: red;
  }
</style>