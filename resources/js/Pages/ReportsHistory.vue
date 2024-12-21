<template>
    <AuthenticatedLayout>
      <!-- Page Header -->
      <div class="bg-white shadow p-4 mb-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
          <h1 class="text-xl font-bold">Report History</h1>
          <div class="text-gray-600 text-sm">{{ currentTime }}</div>
        </div>
      </div>
  
      <!-- Main Content Area -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Items Per Page Control -->
        <div class="flex justify-end mb-4">
          <div class="flex items-center">
            <label for="itemsPerPage" class="mr-2">Show:</label>
            <select id="itemsPerPage" v-model="itemsPerPage" @change="changeItemsPerPage" class="border rounded p-2">
              <option v-for="option in itemsPerPageOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
          </div>
        </div>
  
        <!-- Display Reports in Table Format -->
        <div v-if="paginatedReports.length > 0" class="overflow-x-auto">
          <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead>
              <tr class="bg-gray-200 text-gray-700">
                <th class="py-3 px-4 border-b text-left">View Details</th>
                <th class="py-3 px-4 border-b text-left">Resident Name</th>
                <th class="py-3 px-4 border-b text-left">Emergency</th>
                <th class="py-3 px-4 border-b text-left">Reported On</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(report, index) in paginatedReports" :key="index" class="hover:bg-gray-100 transition duration-200">
                <!-- View Details Button -->
                <td class="py-3 px-4 border-b text-center">
                  <button @click="viewDetails(report)" class="text-blue-500 hover:underline">👁️</button>
                </td>
                <!-- Resident Name with Contact -->
                <td class="py-3 px-4 border-b">
                  {{ report.residentName }} ({{ report.phoneNumber }})
                </td>
                <!-- Emergency Type -->
                <td class="py-3 px-4 border-b">{{ report.emergencyType }}</td>
                <!-- Reported Timestamp -->
                <td class="py-3 px-4 border-b">{{ formatTimestamp(report.timestamp) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- No Reports Available -->
        <div v-else class="text-center text-gray-500 py-8">
          No reports available.
        </div>
  
        <!-- Modal for Viewing Report Details -->
        <div v-if="selectedReport" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
              <h2 class="text-2xl font-semibold text-gray-800">{{ selectedReport.residentName }}</h2>
              <button @click="closeModal" class="modal-close-btn">&times;</button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
              <!-- Card Layout -->
              <div class="grid grid-cols-1 gap-6">
                <!-- Resident Info Card -->
                <div class="card">
                  <div class="flex items-center mb-2">
                    <span class="text-lg font-bold text-gray-700">👤 Resident Info</span>
                  </div>
                  <p><strong>Name:</strong> {{ selectedReport.residentName }}</p>
                  <p><strong>Email:</strong> {{ selectedReport.email }}</p>
                  <p><strong>Phone:</strong> {{ selectedReport.phoneNumber }}</p>
                </div>
  
                <!-- Emergency Details Card -->
                <div class="card">
                  <div class="flex items-center mb-2">
                    <span class="text-lg font-bold text-gray-700">🚨 Emergency Details</span>
                  </div>
                  <p><strong>Type:</strong> {{ selectedReport.emergencyType }}</p>
                  <p><strong>Department:</strong> {{ selectedReport.department }}</p>
                  <p><strong>Status:</strong> 
                    <span :class="statusClass(selectedReport.status)" class="status-pill">
                      {{ selectedReport.status }}
                    </span>
                  </p>
                </div>
  
                <!-- Message Card -->
                <div class="card">
                  <div class="flex items-center mb-2">
                    <span class="text-lg font-bold text-gray-700">📩 Message</span>
                  </div>
                  <p>{{ selectedReport.message }}</p>
                </div>
  
                <!-- Reported On Card -->
                <div class="card">
                  <div class="flex items-center mb-2">
                    <span class="text-lg font-bold text-gray-700">🕒 Reported On</span>
                  </div>
                  <p>{{ formatTimestamp(selectedReport.timestamp) }}</p>
                </div>
              </div>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer">
              <button @click="closeModal" class="btn-close">Close</button>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  </template>
  
  <script setup>
  import { ref, onMounted, computed } from 'vue';
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  import { initializeApp } from 'firebase/app';
  import { getFirestore, collection, getDocs } from 'firebase/firestore';
  
  const firebaseConfig = {
    apiKey: "AIzaSyAFYSmtIlqROIYw_X2viPmA6xgUjIrJ3N0",
    authDomain: "lels-471ca.firebaseapp.com",
    projectId: "lels-471ca",
    storageBucket: "lels-471ca.appspot.com",
    messagingSenderId: "986233703071",
    appId: "1:986233703071:web:0a15471acebc3252aa30aa",
    measurementId: "G-NQBKSBNL8E",
    databaseURL: "https://lels-471ca-default-rtdb.firebaseio.com/",
  };
  
  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const firestore = getFirestore(app);
  
  // States
  const reports = ref([]);
  const selectedReport = ref(null);
  const currentTime = ref(new Date().toLocaleString());
  const itemsPerPage = ref(10);
  const itemsPerPageOptions = ref([10, 25, 50, 'All']);
  
  // Fetch reports from Firestore
  const fetchReports = async () => {
    try {
      const reportsCollection = collection(firestore, 'Reported');
      const snapshot = await getDocs(reportsCollection);
      const reportedData = snapshot.docs.map(doc => {
        const data = doc.data();
        return {
          residentName: data.residentName || 'Unknown',
          phoneNumber: data.phoneNumber || 'Unknown',
          email: data.email || 'Not provided',
          department: data.department || 'Not specified',
          emergencyType: data.emergencyType || 'Unknown',
          status: data.status || 'Unknown',
          message: data.message || 'No message provided',
          timestamp: data.timestamp || null,
        };
      });
      reports.value = reportedData;
    } catch (error) {
      console.error('Error fetching reports:', error);
    }
  };
  
  // Computed properties for pagination
  const paginatedReports = computed(() => {
    if (itemsPerPage.value === 'All') {
      return reports.value;
    }
    return reports.value.slice(0, itemsPerPage.value);
  });
  
  // Change items per page
  const changeItemsPerPage = () => {
    // Reset to show the first set of items based on new selection
    itemsPerPage.value = itemsPerPage.value === 'All' ? reports.value.length : itemsPerPage.value;
  };
  
  // Format timestamp
  const formatTimestamp = (timestamp) => {
    return timestamp ? new Date(timestamp).toLocaleString() : 'Unknown';
  };
  
  // View report details in modal
  const viewDetails = (report) => {
    selectedReport.value = report;
  };
  
  // Close the modal
  const closeModal = () => {
    selectedReport.value = null;
  };
  
  // Assign status class
  const statusClass = (status) => {
    return {
      'bg-green-100 text-green-700': status === 'reported',
      'bg-yellow-100 text-yellow-700': status === 'in-progress',
      'bg-red-100 text-red-700': status === 'urgent',
    };
  };
  
  // Update current time every second
  onMounted(() => {
    fetchReports();
    setInterval(() => {
      currentTime.value = new Date().toLocaleString();
    }, 1000);
  });
  </script>
  
  <style scoped>
  /* Base Styles */
  .bg-white { background-color: white; }
  .shadow { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
  .p-4 { padding: 1rem; }
  .mb-4 { margin-bottom: 1rem; }
  .min-w-full { min-width: 100%; }
  .border-b { border-bottom: 1px solid #ccc; }
  .hover\\:bg-gray-100:hover { background-color: #f7fafc; }
  
  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  .modal-content {
    background: white;
    padding: 20px;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    position: relative;
  }
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ccc;
    padding-bottom: 10px;
  }
  .modal-body {
    padding: 20px 0;
  }
  .modal-footer {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #ccc;
    padding-top: 10px;
  }
  .modal-close-btn {
    font-size: 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
  }
  .card {
    padding: 1rem;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  .btn-close {
    background: linear-gradient(to right, #ff5858, #f09819);
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  .status-pill {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 5px;
    font-size: 0.875rem;
    font-weight: bold;
  }
  </style>
  