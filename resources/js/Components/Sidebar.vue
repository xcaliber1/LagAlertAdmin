<template>
    <div class="sidebar">
      <h2 class="sidebar-title">Reported History</h2>
      <ul class="report-list">
        <li v-for="(report, index) in reports" :key="index" class="report-item">
          <div class="report-info">
            <p><strong>Name:</strong> {{ report.residentName }}</p>
            <p><strong>Emergency:</strong> {{ report.emergency }}</p>
            <p><strong>Reported On:</strong> {{ formatDate(report.reportedOn) }}</p>
          </div>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  export default {
    props: ['reports'],
    methods: {
      formatDate(dateString) {
        const options = {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: 'numeric',
          minute: 'numeric',
        };
        return new Date(dateString).toLocaleString(undefined, options);
      },
    },
  };
  </script>
  
  <style scoped>
  .sidebar {
    width: 300px;
    height: 100vh;
    background-color: #f8f9fa;
    padding: 20px;
    overflow-y: auto;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 0;
    left: 0;
  }
  
  .sidebar-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #333;
  }
  
  .report-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .report-item {
    background-color: #fff;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .report-info p {
    margin: 0;
    font-size: 0.9rem;
  }
  
  .report-info strong {
    color: #333;
  }
  </style>
  