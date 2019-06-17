<template>
  <div class="table-responsive">
    <tool-bar
    :canSearch = "canSearch"
    :canFilter = "canFilter"
    :canAdd = "canAdd"
    :modelName = "modelName"
    :fieldConfigs = "fieldConfigs"
    :baseUrl = "baseUrl"
    ></tool-bar>
    <vuetable ref="vuetable"
      :apiUrl="fetchUrl"
      :fields = "compuatedListFields"
      pagination-path=""
      :css="css.table"
      :multi-sort="true"
      detail-row-component="my-detail-row"
      :per-page = "perPage"
      :append-params="moreParams"
      :modelName = "modelName"
      @vuetable:cell-clicked="onCellClicked"
      @vuetable:pagination-data="onPaginationData"
    >
      <template v-show="list_row_links_num > 0" slot="actions" slot-scope="props">
        <div v-if="list_row_links_num <= 3" class="custom-actions">
          <button v-show="canShow" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Show"
            @click="onAction('show', props)">
            <span class="far fa-eye"></span>
          </button>
          <button v-show="canEdit" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Edit"
            @click="onAction('edit', props)">
            <span class="far fa-edit"></span>
          </button>
          <button v-show="canCopy" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Duplicate"
            @click="onAction('copy', props)">
            <span class="far fa-copy"></span>
          </button>
          <button v-show="canRemove" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Remove"
            @click="onAction('remove', props)">
            <span class="far fa-trash-alt"></span>
          </button>
        </div>

        <div v-else class="dropdown dropleft">
          <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="far fa-compass"></span>
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a v-show="canShow" class="dropdown-item" @click="onAction('show', props)">
              <span class="far fa-eye"></span> Show
            </a>
            <a v-show="canEdit" class="dropdown-item" @click="onAction('edit', props)">
              <span class="far fa-edit"></span> Edit
            </a>
            <a v-show="canCopy" class="dropdown-item" @click="onAction('copy', props)">
              <span class="far fa-copy"></span> Duplicate
            </a>
            <a v-show="canRemove" class="dropdown-item" @click="onAction('remove', props)">
              <span class="far fa-trash-alt"></span> Delete
            </a>
          </div>
        </div>
      </template>
    </vuetable>
    <div class="vuetable-pagination">
      <vuetable-pagination-info ref="paginationInfo"
        info-class="pagination-info"
      ></vuetable-pagination-info>
      <vuetable-pagination ref="pagination"
        :css="css.pagination"
        :icons="css.icons"
        @vuetable-pagination:change-page="onChangePage"
      ></vuetable-pagination>
    </div>
  </div>
</template>

<script>
import accounting from 'accounting'
import moment from 'moment'
import Vuetable from 'vuetable-2/src/components/Vuetable'
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import Vue from 'vue'
import VueEvents from 'vue-events'
import CustomActions from './CustomActions'
import ToolBar from './ToolBar'
Vue.use(VueEvents)
Vue.use(Vuetable)
Vue.component('custom-actions', CustomActions)
Vue.component('tool-bar', ToolBar)
export default {
  components: {
    Vuetable,
    VuetablePagination,
    VuetablePaginationInfo
  },
  props: {
      fetchUrl: {
        type: String,
        required: true
      },
      baseUrl: {
        type: String,
        required: true
      },
      list_row_links_num: {
        required: false
      },
      modelName: {
          type: String,
          required: true
      },
      canSearch: {
        type: Boolean,
        default: true
      },
      canFilter: {
        type: Boolean,
        default: true
      },
      canAdd: {
        type: Boolean,
        default: false
      },
      canEdit: {
        type: Boolean,
        default: false
      },
      canShow: {
        type: Boolean,
        default: false
      },
      canRemove: {
        type: Boolean,
        default: false
      },
      canCopy: {
        type: Boolean,
        default: true
      },
      fieldConfigs: {
        type: Object,
        required: false
      }
    },
  data () {
    return {
      css: {
        table: {
          tableClass: 'table table-bordered table-hover table-sm',
          ascendingIcon: 'fa fa-sort-up',
          descendingIcon: 'fa fa-sort-down'
        },
        pagination: {
          wrapperClass: "pagination justify-content-end",
          activeClass: "active",
          disabledClass: "disabled",
          pageClass: "page-item",
          linkClass: "page-link",
          icons: {
            first: 'fa fa-angle-double-left',
            prev: 'fa fa-angle-left',
            next: 'fa fa-angle-right',
            last: 'fa fa-angle-double-right',
          }
        }
      },
      perPage: 10,
      moreParams: {}
    }
  },
  computed: {
    compuatedListFields: function() {
      let tmp = [];
      $.each(this.fieldConfigs.list, function(i, f) {
        tmp.push(f);
      });

      tmp.push({
          name: '__slot:actions',  // custom actions
          title: 'Actions',
          titleClass: 'center aligned',
          dataClass: 'center aligned'
        });

      return tmp;
    }
  },
  methods: {
    allcap (value) {
      return value.toUpperCase()
    },
    formatNumber (value) {
      return accounting.formatNumber(value, 2)
    },
    formatDate (value, fmt = 'D MMM YYYY') {
      return (value == null)
        ? ''
        : moment(value, 'YYYY-MM-DD').format(fmt)
    },
    computeFields (action) {
      let tmp = [];
      $.each(this.fieldConfigs[action], function(i, f) {
          tmp.push(f);
      });
      return tmp;
    },
    updateFilters (object) {
      var self = this;
      $.each(this.fieldConfigs['filter'], function(name, data) {
          if (!data.values || data.values == undefined) return true;
          var val = object[name];
          if (!val) return true;
          var find = false;
          var l = 1;
          $.each(data.values, function (i, v){
            if (!v) return true;
            if (v == val)
                find = true;
            l++;
          });
          if (!find) {
            self.$set(self.fieldConfigs['filter'][name].values, l, val);
          }
      });
    },
    onPaginationData (paginationData) {
      this.$refs.pagination.setPaginationData(paginationData)
      this.$refs.paginationInfo.setPaginationData(paginationData)
    },
    onChangePage (page) {
      this.$refs.vuetable.changePage(page)
    },
    onCellClicked (data, field, event) {
      let self = this;
      let root = this.$root.sharedState;
      axios.get(this.baseUrl+"/show/"+data.id)
        .then(function(response) {
            var config = {
                okText: "",
                titleIcon: "far fa-eye",
                title: "Show "+ self.modelName + " #"+data.id,
                large: true,
                body: {html:self.showObject(response.data)}
              }
              root.showModal(config);
        }, function(error) {
            root.setAlert("danger", error.response.data, 0, true);
        });
    },
    onAction (action, props) {
      let root = this.$root.sharedState;
      let self = this;

      if (action == "remove" || action == "copy") {
        var config = {
          okText: "Confirm",
          titleIcon: "fas fa-question-circle",
          small: true,
          body: {
            html: "Are you sure to "+action+" this "+self.modelName+"?"
          },
          callback: function () {
            axios.post(self.baseUrl+"/"+action+"/"+props.rowData.id)
              .then(function(response) {
                // success
                root.closeModal();
                self.$refs.vuetable.reload();
                root.setAlert("success",response.data, 3000);
            }, function(error) {
                // error
                root.setAlert("danger", error.response.data, 0, true);
            });
          }
        };

        if (action == "remove") {
          config.title = "Remove "+ self.modelName + " #"+props.rowData.id;
          config.body.html += " <p class='text-danger'>This action can not be undone!</p>";
          config.classes = ['alert', 'alert-danger'];
        } else if (action == "copy") {
          config.title = "Copy "+ self.modelName + " #"+props.rowData.id;
          config.classes = ['alert', 'alert-warning'];
        }

        root.showModal(config);
        return true;
      }

      axios.get(this.baseUrl+"/"+action+"/"+props.rowData.id)
        .then(function(response) {
            var config = {};
            if (action == "show") {
              config = {
                okText: "",
                titleIcon: "far fa-eye",
                title: "Show "+ self.modelName + " #"+props.rowData.id,
                large: true,
                body: {html:self.showObject(response.data)}
              }
              root.showModal(config);
            }

            if (action == "edit") {
              config = {
                okText: "Save Changes",
                titleIcon: "far fa-edit",
                title: "Edit "+ self.modelName + " #"+props.rowData.id,
                callback: function () {
                  let modal = self.$root.$refs.modal.$refs.modalBody.$refs.modalBodyContent;
                  modal.$refs.form.validateBeforeSubmit();
                },
                large: true,
                body: {
                  name: "form",
                  fields: self.computeFields('edit'),
                  url: self.baseUrl+"/edit/"+props.rowData.id,
                  values: response.data,
                  submitCallback: function () {
                    root.closeModal();
                    let form = self.$root.$refs.modal.$refs.modalBody.$refs.modalBodyContent.$refs.form;
                    self.updateFilters(form.updatedFields);
                    self.$refs.vuetable.reload();
                  }
                }
              }
              root.showModal(config);
            }
        }, function(error) {
            root.setAlert("danger", error.response.data, 0, true);
        });
    },
    showObject(data) {
      let config = this.fieldConfigs.show;
      var body = "";
      $.each(config, function(i, v){
        if (!v.name || !v.name == undefined)  return true;
        var val = Object.byString(data, v.name)

        body += " <div class='row'>" +
          "<div class='col-6 col-sm-3 font-weight-bold text-right'>"+v.title+"</div>" +
          "<div class='col col-sm-9'>"+val+"</div>" +
        "</div>";
      })
      return body;
    }
  },
  events: {
    'refresh' (data) {
      console.log("trying to refresh");
      this.$refs.vuetable.refresh();
    },
    'reload' (data) {
      console.log("trying to reload");
      this.updateFilters(data);
      this.$refs.vuetable.reload();
    },
    'search-set' (searchText) {
      this.moreParams = {
        search: searchText
      }
      Vue.nextTick( () => this.$refs.vuetable.refresh() )
    },
    'search-reset' () {
      this.moreParams = {}
      Vue.nextTick( () => this.$refs.vuetable.refresh() )
    },
    'select-rows' (number) {
        this.perPage = number;
        Vue.nextTick( () => this.$refs.vuetable.refresh())
    },
    'apply-filter' (inputs) {
       let filters = [];
       $.each(inputs, function(i,f){
          if (f.value != "" ) {
            filters.push(f.name+"|"+f.value);
          }
       });
       this.moreParams = {
          filter: filters.join()
       }
       Vue.nextTick( () => this.$refs.vuetable.refresh() )
    }
  }
}
</script>
<style>
.table thead th {
  background-color: #f7f7f9;
  border-top: 2px solid #69a15bc9;
}
</style>
