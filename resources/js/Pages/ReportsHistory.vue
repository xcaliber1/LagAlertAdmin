<template>
  <AuthenticatedLayout>
    <!-- Page Header -->
    <div class="bg-white shadow p-4 mb-4">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-xl font-bold">INCIDENTS MONITORED & Report History</h1>
        <div class="text-gray-600 text-sm">{{ currentTime }}</div>
      </div>
    </div>

    <!-- Incident Monitoring Table -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg text-sm">
          <thead class="bg-yellow-200">
            <tr>
              <th class="py-3 px-4 border text-left">Address</th>
              <th v-for="type in incidentTypes" :key="type" class="py-3 px-4 border text-center">
                {{ type }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(location, index) in aggregatedReports"
              :key="index"
              class="hover:bg-gray-100 transition duration-200"
            >
              <td class="py-3 px-4 border">{{ location.fullAddress }}</td>
              <td
                v-for="type in incidentTypes"
                :key="type"
                class="py-3 px-4 border text-center"
              >
                {{ location[type] || '-' }}
              </td>
            </tr>
            <tr class="bg-blue-100 font-semibold">
              <td class="py-3 px-4 border">TOTAL</td>
              <td
                v-for="type in incidentTypes"
                :key="type"
                class="py-3 px-4 border text-center"
              >
                {{ total[type] || 0 }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Report History Table -->
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
              <th class="py-3 px-4 border-b text-left">Location</th>
              <th class="py-3 px-4 border-b text-left">Reported On</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(report, index) in paginatedReports"
              :key="index"
              class="hover:bg-gray-100 transition duration-200"
            >
              <td class="py-3 px-4 border-b text-center">
                <button @click="viewDetails(report)" class="text-blue-500 hover:underline">👁️</button>
              </td>
              <td class="py-3 px-4 border-b">
                {{ report.residentName }} ({{ report.phoneNumber }})
              </td>
              <td class="py-3 px-4 border-b">{{ report.emergencyType }}</td>
              <td class="py-3 px-4 border-b">{{ report.location }}</td>
              <td class="py-3 px-4 border-b">{{ formatTimestamp(report.timestamp) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- No Reports Available -->
      <div v-else class="text-center text-gray-500 py-8">
        No reports available.
      </div>
    </div>

    <!-- Modal for Viewing Report Details -->
    <div v-if="selectedReport" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header bg-white p-4 rounded-t-lg border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-900">{{ selectedReport.residentName }}</h2>
          <button @click="closeModal" class="modal-close-btn text-gray-600 text-2xl font-bold hover:text-gray-900">
            &times;
          </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body p-6 bg-gray-50 space-y-4">
          <!-- Resident Info -->
          <div class="card p-4 bg-white shadow-md rounded-lg">
            <div class="flex items-center mb-3">
              <span class="text-blue-600 text-lg mr-2">👤</span>
              <h3 class="font-semibold text-gray-800">Resident Info</h3>
            </div>
            <p><strong>Name:</strong> {{ selectedReport.residentName }}</p>
            <p><strong>Email:</strong> {{ selectedReport.email }}</p>
            <p><strong>Phone:</strong> {{ selectedReport.phoneNumber }}</p>
          </div>

          <!-- Emergency Details -->
          <div class="card p-4 bg-white shadow-md rounded-lg">
            <div class="flex items-center mb-3">
              <span class="text-red-600 text-lg mr-2">🚨</span>
              <h3 class="font-semibold text-gray-800">Emergency Details</h3>
            </div>
            <p><strong>Type:</strong> {{ selectedReport.emergencyType }}</p>
            <p><strong>Location:</strong> {{ selectedReport.location }}</p>
            <p><strong>Department:</strong> {{ selectedReport.department }}</p>
            <p>
              <strong>Status:</strong>
              <span
                :class="{
                  'text-green-600 font-bold': selectedReport.status === 'reported',
                  'text-red-600 font-bold': selectedReport.status !== 'reported',
                }"
              >
                {{ selectedReport.status }}
              </span>
            </p>
          </div>

          <!-- Message -->
          <div class="card p-4 bg-white shadow-md rounded-lg">
            <div class="flex items-center mb-3">
              <span class="text-yellow-500 text-lg mr-2">✉️</span>
              <h3 class="font-semibold text-gray-800">Message</h3>
            </div>
            <p>{{ selectedReport.message }}</p>
          </div>

          <!-- Reported On -->
          <div class="card p-4 bg-white shadow-md rounded-lg">
            <div class="flex items-center mb-3">
              <span class="text-gray-500 text-lg mr-2">🕒</span>
              <h3 class="font-semibold text-gray-800">Reported On</h3>
            </div>
            <p>{{ formatTimestamp(selectedReport.timestamp) }}</p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer bg-gray-100 p-4 flex justify-end gap-4 rounded-b-lg">
          <button
            @click="closeModal"
            class="btn-close bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { initializeApp } from 'firebase/app';
import { getFirestore, collection, onSnapshot } from 'firebase/firestore';

// Firebase Configuration
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

const reports = ref([]);
const aggregatedReports = ref([]);
const total = ref({});
const incidentTypes = ref([]);
const currentTime = ref(new Date().toLocaleString());
const itemsPerPage = ref(10);
const itemsPerPageOptions = ref([10, 25, 50, 'All']);
const selectedReport = ref(null);

// Fetch Reports
const fetchReports = () => {
  const reportsCollection = collection(firestore, 'Reported');
  onSnapshot(reportsCollection, (snapshot) => {
    const allReports = snapshot.docs.map((doc) => doc.data());
    reports.value = allReports;

    const locationData = {};
    const typeSet = new Set();

    allReports.forEach((report) => {
      const { location, emergencyType } = report;
      typeSet.add(emergencyType);
      if (!locationData[location]) locationData[location] = {};
      locationData[location][emergencyType] = (locationData[location][emergencyType] || 0) + 1;
    });

    incidentTypes.value = Array.from(typeSet);
    aggregatedReports.value = Object.entries(locationData).map(([location, data]) => ({
      fullAddress: location,
      ...data,
    }));

    calculateTotals();
  });
};

const calculateTotals = () => {
  const newTotals = {};
  aggregatedReports.value.forEach((location) => {
    incidentTypes.value.forEach((type) => {
      newTotals[type] = (newTotals[type] || 0) + (location[type] || 0);
    });
  });
  total.value = newTotals;
};

const paginatedReports = computed(() => {
  if (itemsPerPage.value === 'All') return reports.value;
  return reports.value.slice(0, itemsPerPage.value);
});

const changeItemsPerPage = () => {};
const viewDetails = (report) => { selectedReport.value = report; };
const formatTimestamp = (timestamp) => (timestamp ? new Date(timestamp).toLocaleString() : 'Unknown');
const closeModal = () => { selectedReport.value = null; };

onMounted(() => {
  fetchReports();
  setInterval(() => {
    currentTime.value = new Date().toLocaleString();
  }, 1000);
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  width: 90%;
  max-width: 600px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}
.modal-body {
  flex: 1;
  padding: 1.5rem;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding: 1rem;
}
.card {
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  font-size: 0.875rem;
}
.btn-close {
  background-color: #ff5a5a;
  color: white;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: bold;
  border: none;
  border-radius: 0.375rem;
  transition: background-color 0.3s;
}
.btn-close:hover {
  background-color: #e04747;
}
</style>
