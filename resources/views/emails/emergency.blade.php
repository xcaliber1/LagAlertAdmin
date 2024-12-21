<h1>Emergency Alert</h1>
<p><strong>Type:</strong> {{ $emergencyType }}</p>
<p><strong>Description:</strong> {{ $description }}</p>
<p><strong>Location:</strong> Latitude {{ $location['latitude'] }}, Longitude {{ $location['longitude'] }}</p>
<p><strong>Reporter:</strong> {{ $personalInfo['firstname'] }} {{ $personalInfo['lastname'] }}</p>
<p><strong>Phone:</strong> {{ $personalInfo['phoneNumber'] }}</p>
<p><strong>Email:</strong> {{ $personalInfo['email'] }}</p>
