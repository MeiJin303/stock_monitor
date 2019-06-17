<template>
  <div class="custom-actions">
    <button class="btn btn-sm btn-outline-secondary" @click="itemAction('view-item', rowData, rowIndex)"><i class="fas fa-eye"></i></button>
    <button class="btn btn-sm btn-outline-secondary" @click="itemAction('edit-item', rowData, rowIndex)"><i class="far fa-edit"></i></button>
    <button class="btn btn-sm btn-outline-secondary" @click="itemAction('delete-item', rowData, rowIndex)"><i class="far fa-trash-alt"></i></button>
  </div>
</template>

<script>
export default {
  props: {
    rowData: {
      type: Object,
      required: true
    },
    rowIndex: {
      type: Number
    }
  },
  methods: {
    itemAction (action, data, index) {
      console.log('custom-actions: ' + action, data.name, index)
    }
  }
}
</script>