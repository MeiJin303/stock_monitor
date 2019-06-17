<template>
    <div class="row tool-bar no-gutters" style="margin: 10px 0px 10px 0px">
        <div v-show="filterShown" class="col-12 card" style="margin-bottom: 10px">
            <div class="card-header">
              <h6><i class="fas fa-filter"></i> Filters</h6>
            </div>
            <div class="card-body">
              <form v-on:submit.prevent class="form-inline">
                <div class="col-12 col-md-4 form-group" v-for="(filter, index) in filterFields" :key="index">
                    <div class="col-12" style="padding-left: 0px">{{filter.title ? filter.title : filter}}</div>
                    <select v-if="filter.values" class="custom-select form-control" ref="filterInputs" v-bind:name="filter.name ? filter.name : filter" @change="applyFilter">
                      <option value="">Any</option>
                      <option v-for="(v, k) in filter.values" :key="k">{{v}}</option>
                    </select>
                    <input v-if="!filter.values" type="text" class="form-control" ref="filterInputs" v-bind:name="filter.name ? filter.name : filter" @keyup.enter="applyFilter">
                </div>
              </form>
            </div>
        </div>
        <form class="form-inline col-sm-4 col-12">
          <label class="small">Show</label>
          <select class="ui simple dropdown form-control form-control-sm" v-model="perPage" @change="selectPerPage">
            <option value=-1>All</option>
            <option value=10>10</option>
            <option value=15>15</option>
            <option value=20>20</option>
            <option value=25>25</option>
            <option value=50>50</option>
            <option value=100>100</option>
            <option value=250>250</option>
          </select>
        </form>
        <div class="row col-sm-8 col-12 align-items-end no-gutters">
          <div class="col d-flex justify-content-end">
              <!-- add button -->
              <button v-if="canAdd" type="button" class="btn btn-outline-success btn-sm ml-1" 
              data-toggle="tooltip" data-placement="top" title="Add New Entry" ref="addModalBtn" @click="openModal()"><i class="fas fa-plus"></i></button>

              <!-- filter button -->
              <button v-if="canFilter" v-show="filterShown" type="button" class="btn btn-outline-info btn-sm ml-1" 
              data-toggle="tooltip" data-placement="top" title="Hide filters" @click.prevent="toggleFilter"><i class="fas fa-filter"></i></button>
              <button v-if="canFilter" v-show="!filterShown" type="button" class="btn btn-outline-info btn-sm ml-1" 
              data-toggle="tooltip" data-placement="top" title="Show filters" @click.prevent="toggleFilter"><i class="fas fa-filter"></i></button>

              <!-- reset button -->
              <button v-if="canFilter || canSearch" type="button" class="btn btn-outline-warning btn-sm ml-1" data-toggle="tooltip" data-placement="top" title="Reset" @click.prevent="reset"><i class="fas fa-undo"></i></button>
          </div>
          <!-- search text box -->
          <div v-if="canSearch" class="col-md-3 col-sm-4 col-xs-4 col-md-auto ml-1">
              <input type="text" v-model="searchText" class="form-control form-control-sm" @keyup.enter="doSearch" placeholder="Search">
          </div>
        </div>
    </div>
</template>

<script>
  export default {
    props: {
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
        modelName: {
          type: String,
          required: true
        },
        fieldConfigs: {
          type: Object,
          required: true
        },
        baseUrl: {
          type: String,
          required: false
        }
    },
    data () {
      return {
        searchText: '',
        perPage: 10,
        filterShown: false
      }
    },
    computed: {
        filterFields: function() {
          let tmp = [];
          $.each(this.fieldConfigs.filter, function(i, f) {
                tmp.push(f);
          });
          return tmp;
        },
        addFields: function() {
          return this.$parent.computeFields('add');
        },
        addUrl: function() {
          return this.baseUrl+"/add"
        }
    },
    methods: {
      doSearch () {
        this.$events.fire('search-set', this.searchText)
      },
      reset () {
        this.searchText = ''
        $.each(this.$refs.filterInputs, function(i,f){
          f.value = "";
        });
        this.$events.fire('search-reset')
      },
      selectPerPage() {
        this.$events.fire('select-rows', this.perPage)
      },
      toggleFilter() {
        this.filterShown = !this.filterShown;
      },
      applyFilter() {
        this.$events.fire('apply-filter', this.$refs.filterInputs);
      },
      openModal() {
        let self = this;
        var config = {
          okText: "Save Changes",
          titleIcon: "fas fa-plus-circle",
          title: "Add New " + this.modelName,
          large: true,
          callback: function () {
            let modal = self.$root.$refs.modal.$refs.modalBody.$refs.modalBodyContent;
            modal.$refs.form.validateBeforeSubmit();
          },
          body: {
            name: "form",
            fields: this.addFields,
            url: this.addUrl,
            submitCallback: function () {
              self.$root.sharedState.closeModal();
              let form = self.$root.$refs.modal.$refs.modalBody.$refs.modalBodyContent.$refs.form;
              self.$events.fire('reload', form.updatedFields);
            }
          }
        }
        this.$root.sharedState.showModal(config);
      }
    }
  }
</script>