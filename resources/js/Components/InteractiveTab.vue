<template>
  <div class="border-b border-gray-200">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
      <slot name="tablist" />
    </ul>
  </div>
  
  <div>
    <slot name="tabcontent" />
  </div>
</template>
