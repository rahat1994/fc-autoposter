<template>
  <div class="w-full">
    <div class="flex items-center py-4">
      <Input
        v-model="globalFilter"
        :placeholder="searchPlaceholder"
        class="max-w-sm"
      />
    </div>
    
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead
              v-for="header in headerGroup.headers"
              :key="header.id"
              :class="header.column.getCanSort() ? 'cursor-pointer select-none' : ''"
              @click="header.column.getToggleSortingHandler()?.($event)"
            >
              <div v-if="!header.isPlaceholder" class="flex items-center space-x-2">
                <component
                  :is="header.column.columnDef.header"
                  v-if="typeof header.column.columnDef.header !== 'string'"
                  :column="header.column"
                  :table="table"
                />
                <span v-else>{{ header.column.columnDef.header }}</span>
                
                <div v-if="header.column.getIsSorted()" class="flex flex-col">
                  <ChevronUp 
                    :class="header.column.getIsSorted() === 'asc' ? 'text-foreground' : 'text-muted-foreground/50'"
                    class="h-3 w-3" 
                  />
                  <ChevronDown 
                    :class="header.column.getIsSorted() === 'desc' ? 'text-foreground' : 'text-muted-foreground/50'"
                    class="h-3 w-3" 
                  />
                </div>
              </div>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <component
                  :is="cell.column.columnDef.cell"
                  v-if="typeof cell.column.columnDef.cell !== 'string'"
                  :row="row"
                  :cell="cell"
                  :getValue="cell.getValue"
                />
                <span v-else>{{ cell.getValue() }}</span>
              </TableCell>
            </TableRow>
          </template>
          <template v-else>
            <TableRow>
              <TableCell :colSpan="columns.length" class="h-24 text-center">
                No results.
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>
    
    <div class="flex items-center justify-between space-x-2 py-4">
      <div class="text-sm text-muted-foreground">
        {{ getResultsText() }}
      </div>
      <div class="space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="!table.getCanPreviousPage()"
          @click="table.previousPage()"
        >
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="!table.getCanNextPage()"
          @click="table.nextPage()"
        >
          Next
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import { ChevronUp, ChevronDown } from 'lucide-vue-next'

import {
  Button,
  Input,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui'

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...',
  },
})

const globalFilter = ref('')
const sorting = ref([])

const table = useVueTable({
  get data() {
    return props.data
  },
  get columns() {
    return props.columns
  },
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  state: {
    get sorting() {
      return sorting.value
    },
    get globalFilter() {
      return globalFilter.value
    },
  },
  onSortingChange: updaterOrValue => {
    sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue
  },
  onGlobalFilterChange: updaterOrValue => {
    globalFilter.value = typeof updaterOrValue === 'function' ? updaterOrValue(globalFilter.value) : updaterOrValue
  },
})

const getResultsText = () => {
  const { pageIndex, pageSize } = table.getState().pagination
  const totalRows = table.getFilteredRowModel().rows.length
  const startRow = pageIndex * pageSize + 1
  const endRow = Math.min((pageIndex + 1) * pageSize, totalRows)
  
  return `Showing ${startRow} to ${endRow} of ${totalRows} entries`
}
</script>