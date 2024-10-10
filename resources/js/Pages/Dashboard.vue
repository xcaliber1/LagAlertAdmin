<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage as inertiaUsePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { initializeApp } from 'firebase/app';
import { getDatabase, ref as dbRef, onValue } from 'firebase/database';

const { props } = inertiaUsePage();
const map = ref(null);
const emergencyMarkers = ref([]);
const emergenciesData = ref({});
const isReportOverlayVisible = ref(false);
const selectedEmergency = ref({
  residentName: 'Unknown',
  timestamp: 'Unknown',
  emergencyType: 'Unknown',
});
const selectedDepartment = ref('');
const selectedReplyMessage = ref('');
const showPreMadeReplies = ref(false);

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

const app = initializeApp(firebaseConfig);
const database = getDatabase(app);

const initializeMap = () => {
  map.value = L.map('map').setView([props.coordinates.latitude, props.coordinates.longitude], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map.value);
};

const clearMarkers = () => {
  if (emergencyMarkers.value && Array.isArray(emergencyMarkers.value)) {
    emergencyMarkers.value.forEach((marker) => {
      map.value.removeLayer(marker);
    });
    emergencyMarkers.value = [];
  }
};

const addEmergencyMarkers = (data) => {
  clearMarkers();
  if (data) {
    for (const key in data) {
      const emergency = data[key];
      if (emergency.location && emergency.location.latitude && emergency.location.longitude) {
        const { latitude, longitude } = emergency.location;
        const marker = L.marker([latitude, longitude])
          .addTo(map.value)
          .bindPopup(createPopupContent(emergency, key));
        emergencyMarkers.value.push(marker);
      }
    }
  }
};

const createPopupContent = (emergency, key) => {
  const container = document.createElement('div');
  const residentName = `${emergency.personalInfo?.firstname || 'Unknown'} ${emergency.personalInfo?.lastname || ''}`.trim();
  const contactNumber = emergency.personalInfo?.phoneNumber || 'Not available';
  
  container.innerHTML = `
    <div class="p-4" style="max-height: 400px; overflow-y: auto;">
      <div class="font-bold mb-2">${residentName || 'undefined'} <span class="text-gray-500">+63${contactNumber || 'undefined'}</span></div>
      <div class="cursor-pointer mb-1 shadow-md p-2 rounded">
        <div class="flex justify-between items-center">
          <span>Emergency Details</span>
        </div>
        <div class="mt-4">
          <p style="margin: 0;">Sent On: ${new Date(emergency.timestamp).toLocaleString()}</p>
          <p style="margin: 0;">Emergency: ${emergency.emergencyType}</p>
          <p style="margin: 0;">Description: ${emergency.description || 'No description provided.'}</p>
          ${emergency.isVideo ? `
            <video controls class="mt-2 w-full" autoplay muted playsinline>
              <source src="${emergency.mediaUri}" type="video/mp4">
              Your browser does not support the video tag.
            </video>
          ` : emergency.mediaUri ? `
            <img src="${emergency.mediaUri}" alt="Emergency Image" class="mt-2 w-full rounded">
          ` : ''}
        </div>
      </div>
      <button class="block w-full p-2 bg-red-500 text-white rounded mt-4 report-btn">REPORT</button>
    </div>
  `;

  container.querySelector('.report-btn').addEventListener('click', () => {
    showReportOverlay(key);
  });

  return container;
};

const fetchEmergencyData = () => {
  const emergenciesRef = dbRef(database, 'emergencies');
  onValue(emergenciesRef, (snapshot) => {
    const data = snapshot.val();
    if (data && isNewEmergency(data)) {
      playAlarmSound();
    }
    processEmergencyData(data);
    addEmergencyMarkers(data);
    emergenciesData.value = data || {};
  });
};

const isNewEmergency = (data) => {
  const currentKeys = Object.keys(emergenciesData.value);
  const newKeys = Object.keys(data);
  return newKeys.length > currentKeys.length;
};

const playAlarmSound = () => {
  const alarmSound = document.querySelector('audio');
  alarmSound.muted = false;
  alarmSound.play().catch((error) => {
    console.log('Autoplay prevented:', error);
  });
};

const formatCurrentTime = (date) => {
  return date.toLocaleString('en-US', {
    month: 'numeric',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    second: 'numeric',
    hour12: true,
  });
};

const processEmergencyData = (data) => {
  const emergencyTypesCount = {
    Flood: 0,
    Fire: 0,
    Accidents: 0,
    Crime: 0,
    Medical: 0,
    Others: 0,
  };
  let total = 0;

  if (data) {
    for (const key in data) {
      const emergency = data[key];
      if (emergency.emergencyType && emergencyTypesCount.hasOwnProperty(emergency.emergencyType)) {
        emergencyTypesCount[emergency.emergencyType]++;
      } else {
        emergencyTypesCount.Others++;
      }
      total++;
    }
  }

  totalEmergencies.value = total;
  emergencyTypes.value = emergencyTypesCount;
};

const unmuteAndPlayAlarm = () => {
  const alarmSound = document.querySelector('audio');
  alarmSound.muted = false;
};

const showReportOverlay = (emergencyId) => {
  const emergency = emergenciesData.value[emergencyId];
  if (emergency) {
    const residentName = `${emergency.personalInfo?.firstname || 'Unknown'} ${emergency.personalInfo?.lastname || ''}`.trim();
    selectedEmergency.value = {
      residentName: residentName || 'Unknown',
      timestamp: emergency.timestamp || 'Unknown',
      emergencyType: emergency.emergencyType || 'Unknown',
    };
  } else {
    selectedEmergency.value = {
      residentName: 'Unknown',
      timestamp: 'Unknown',
      emergencyType: 'Unknown',
    };
  }
  isReportOverlayVisible.value = true;
};


const sendReport = () => {
  console.log('Sending report with message:', selectedReplyMessage.value);
  isReportOverlayVisible.value = false;
};

const currentTime = ref(formatCurrentTime(new Date()));
const totalEmergencies = ref(0);
const emergencyTypes = ref({
  Flood: 0,
  Fire: 0,
  Accidents: 0,
  Crime: 0,
  Medical: 0,
  Others: 0,
});

const selectReply = (reply) => {
  selectedReplyMessage.value = reply;
  showPreMadeReplies.value = false;
};

onMounted(() => {
  initializeMap();
  fetchEmergencyData();
  setInterval(() => {
    currentTime.value = formatCurrentTime(new Date());
  }, 1000);
  document.addEventListener('click', unmuteAndPlayAlarm, { once: true });
});
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <div class="h-screen flex flex-col relative">
      <!-- Main Content -->
      <div class="flex flex-1 relative">
        <div id="map" class="w-2/3 h-full z-10"></div>
        <div class="w-1/3 h-full p-4 bg-gray-100 flex flex-col items-center">
          <div class="mt-5 text-center mb-5 w-full">as of {{ currentTime }}</div>
          <div class="mb-3 text-8xl text-center text-red-500">{{ totalEmergencies }}</div>
          <div class="mb-20 text-center">Current Emergencies in Laguna<!--{{ props.userMunicipality }}--></div>
          <div class="mt-4 grid grid-cols-2 gap-10 w-full">
            <div v-for="(value, type) in emergencyTypes" :key="type" class="flex flex-col items-center justify-center">
              <div :class="['w-12 h-12 mb-2 rounded-full flex items-center justify-center', icons[type]]">
                <img :src="getIconUrl(type)" alt="icon" class="w-6 h-6" />
              </div>
              <div class="text-center">{{ type }}</div>
              <div class="text-center text-xl">{{ value }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Audio for Alarm -->
      <audio ref="alarmSound" src="sounds/alarm.mp3" preload="auto" muted></audio>
      <button @click="unmuteAndPlayAlarm" class="hidden">Play Alarm</button>

      <!-- Modal for Reporting -->
      <div v-if="isReportOverlayVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="defaultModalTitle">
        <div class="bg-gray-200 rounded-lg shadow-lg overflow-hidden w-full max-w-lg mx-auto z-60">
          <!-- Dialog Header -->
          <div class="p-4">
            <h2 class="text-xl font-semibold text-center">Create Report</h2>
          </div>
          <!-- Dialog Body -->
          <div class="px-8 py-4">
            <div class="mb-4">
              <div class="flex justify-between items-center">
                <span class="font-semibold">Resident:</span>
                <span>{{ selectedEmergency.residentName }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="font-semibold">Sent On:</span>
                <span>{{ formatTimestamp(selectedEmergency.timestamp) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="font-semibold">Incident Type:</span>
                <span>{{ selectedEmergency.emergencyType }}</span>
              </div>
            </div>
            <div class="mb-4">
              <label for="department" class="block text-sm font-semibold">Reporting to:</label>
              <select id="department" class="w-full p-2 mt-1 border rounded" v-model="selectedDepartment">
                <option value="">Select Department</option>
                <option v-for="department in departments" :key="department" :value="department">{{ department }}</option>
              </select>
            </div>
            <div class="mb-4 relative">
              <label for="message" class="block text-sm font-semibold cursor-pointer" @click="showPreMadeReplies = true">Resident Message:</label>
              <textarea id="message" class="w-full p-2 mt-1 border rounded" rows="4" placeholder="Send a reply *" v-model="selectedReplyMessage"></textarea>
            </div>
            <div class="flex justify-between">
              <button @click="isReportOverlayVisible = false" class="bg-gray-500 text-white p-2 rounded w-full mr-2">CANCEL</button>
              <button @click="sendReport" class="bg-red-500 text-white p-2 rounded w-full ml-2">SEND REPORT</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal for Pre-made Replies -->
      <div v-if="showPreMadeReplies" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-md">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
          <h3 class="text-lg font-semibold mb-4">Pre-made Replies</h3>
          <div class="overflow-y-auto h-64">
            <div v-for="(replies, type) in preMadeReplies" :key="type">
              <div class="font-bold">{{ type }}</div>
              <div v-for="reply in replies" :key="reply" @click="selectReply(reply)" class="p-2 hover:bg-gray-200 cursor-pointer">
                <p>{{ reply }}</p>
              </div>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button @click="showPreMadeReplies = false" class="bg-red-500 text-white p-2 rounded">Close</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
export default {
  data() {
    return {
      icons: {
        Flood: 'bg-blue-500',
        Fire: 'bg-red-500',
        Accidents: 'bg-yellow-500',
        Crime: 'bg-green-500',
        Medical: 'bg-indigo-500',
        Others: 'bg-gray-500',
      },
      departments: [
        'Fire Department',
        'Police Department',
        'Medical Services',
        'Public Works',
        'Emergency Management',
        'Other'
      ],
      preMadeReplies: {
        Flood: [
          "Avoid any contact with flood water or sediment due to the threat of contamination.",
          "Seek higher grounds if you're in the area of effect and please wait for rescuers to arrive."
        ],
        Fire: [
          "Please evacuate the area immediately and follow the instructions of the emergency personnel.",
          "Avoid inhaling smoke and stay low to the ground if you have to move through a smoky area."
        ],
        Accidents: [
          "Emergency services have been notified and are on their way.",
          "Please do not move any injured person unless they are in immediate danger."
        ],
        Crime: [
          "A report has been made to the local police. Please stay safe and avoid any confrontation.",
          "Provide any information you have to the authorities."
        ],
        Medical: [
          "Emergency medical services are on their way. Please provide first aid if you are trained and able.",
          "Keep the patient comfortable and avoid moving them if they are seriously injured."
        ],
        Others: [
          "The situation has been reported to the relevant authorities.",
          "Please follow any instructions given by the emergency personnel and stay safe."
        ]
      },
      selectedReplyMessage: '',
      showPreMadeReplies: false,
      isReportOverlayVisible: false,
      selectedEmergency: {
        residentName: 'Unknown',
        timestamp: 'Unknown',
        emergencyType: 'Unknown'
      },
      selectedDepartment: ''
    };
  },
  methods: {
    showReportOverlay(emergencyId) {
      const emergency = this.emergenciesData[emergencyId];
      if (emergency) {
        this.selectedEmergency = {
          residentName: emergency.residentName || 'Unknown',
          timestamp: emergency.timestamp || 'Unknown',
          emergencyType: emergency.emergencyType || 'Unknown'
        };
      } else {
        this.selectedEmergency = {
          residentName: 'Unknown',
          timestamp: 'Unknown',
          emergencyType: 'Unknown'
        };
      }
      this.isReportOverlayVisible = true;
    },
    formatTimestamp(timestamp) {
      return timestamp === 'Unknown' ? 'Unknown' : new Date(timestamp).toLocaleString();
    },
    sendReport() {
      // Add your logic to handle the report sending here
      console.log('Sending report with message:', this.selectedReplyMessage);
      this.isReportOverlayVisible = false;
    },
    getIconUrl(type) {
      const iconUrls = {
        Flood: 'icons/flood.png',
        Fire: 'icons/fire.png',
        Accidents: 'icons/accident.png',
        Crime: 'icons/crime.png',
        Medical: 'icons/medical.png',
        Others: 'icons/more.png',
      };
      return iconUrls[type];
    },
    selectReply(reply) {
      this.selectedReplyMessage = reply;
      this.showPreMadeReplies = false;
    }
  },
  created() {
    document.addEventListener('show-report-overlay', (event) => {
      this.showReportOverlay(event.detail);
    });
  }
};
</script>

<style scoped>
@import "leaflet/dist/leaflet.css";

.flex {
  display: flex;
}
.items-center {
  align-items: center;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.text-red-500 {
  color: #f56565;
}
.bg-gray-100 {
  background-color: #f7fafc;
}
.bg-blue-500 {
  background-color: #4299e1;
}
.bg-red-500 {
  background-color: #f56565;
}
.bg-yellow-500 {
  background-color: #ecc94b;
}
.bg-green-500 {
  background-color: #48bb78;
}
.bg-indigo-500 {
  background-color: #667eea;
}
.bg-gray-500 {
  background-color: #a0aec0;
}
.h-screen {
  height: 100vh;
}
.p-4 {
  padding: 1rem;
}
.mb-2 {
  margin-bottom: 0.5rem;
}
.mt-2 {
  margin-top: 0.5rem;
}
.mt-4 {
  margin-top: 1rem;
}
.mb-1 {
  margin-bottom: 0.25rem;
}
.grid {
  display: grid;
}
.grid-cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
.gap-4 {
  gap: 1rem;
}
.mr-2 {
  margin-right: 0.5rem;
}
.ml-auto {
  margin-left: auto;
}
#map {
  width: 100%;
  height: 100%;
}
.shadow-md {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.bg-white {
  background-color: white;
}
.rounded {
  border-radius: 0.25rem;
}
.z-10 {
  z-index: 10;
}
.z-50 {
  z-index: 50;
}
.z-100 {
  z-index: 100;
}
.z-60 {
  z-index: 60;
}
</style>
