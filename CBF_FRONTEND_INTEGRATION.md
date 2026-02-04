# Content-Based Filtering - Frontend Integration Guide

## Overview
This guide shows how to integrate the recommendation system and like button into your frontend application.

## Frontend Technologies Covered
- Vue.js / Vue 3
- React
- Vanilla JavaScript
- HTML/CSS

---

## Vue.js 3 Integration

### 1. Create Recommendation Service
**File**: `src/services/recommendationService.js`

```javascript
import axios from 'axios';

const API_BASE = '/api';

export const recommendationService = {
  // Get personalized recommendations
  getRecommendations(limit = 10) {
    return axios.get(`${API_BASE}/recommendations`, {
      params: { limit }
    });
  },

  // Get similar events
  getSimilarEvents(eventId, limit = 5) {
    return axios.get(`${API_BASE}/recommendations/similar/${eventId}`, {
      params: { limit }
    });
  },

  // Like an event
  likeEvent(eventId) {
    return axios.post(`${API_BASE}/events/${eventId}/like`);
  },

  // Unlike an event
  unlikeEvent(eventId) {
    return axios.post(`${API_BASE}/events/${eventId}/unlike`);
  },

  // Get event like status
  getLikeStatus(eventId) {
    return axios.get(`${API_BASE}/events/${eventId}/like-status`);
  },

  // Get user's liked events
  getUserLikes() {
    return axios.get(`${API_BASE}/likes`);
  }
};
```

### 2. Recommendation Component
**File**: `src/components/EventRecommendations.vue`

```vue
<template>
  <div class="recommendations">
    <h2>Recommended For You</h2>
    
    <div v-if="loading" class="loading">
      <p>Loading recommendations...</p>
    </div>

    <div v-else-if="recommendations.length === 0" class="no-recommendations">
      <p>Like some events to get personalized recommendations!</p>
    </div>

    <div v-else class="events-grid">
      <EventCard
        v-for="event in recommendations"
        :key="event.id"
        :event="event"
        :is-liked="event.is_liked"
        @like="handleLike"
        @view="handleViewEvent"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { recommendationService } from '@/services/recommendationService';
import EventCard from './EventCard.vue';

const recommendations = ref([]);
const loading = ref(false);

// Load recommendations on mount
onMounted(async () => {
  await loadRecommendations();
});

// Fetch recommendations
const loadRecommendations = async () => {
  loading.value = true;
  try {
    const response = await recommendationService.getRecommendations(10);
    recommendations.value = response.data.data;
  } catch (error) {
    console.error('Failed to load recommendations:', error);
  } finally {
    loading.value = false;
  }
};

// Handle like action
const handleLike = async (eventId, isLiked) => {
  try {
    if (isLiked) {
      await recommendationService.unlikeEvent(eventId);
    } else {
      await recommendationService.likeEvent(eventId);
    }
    
    // Update local state
    const event = recommendations.value.find(e => e.id === eventId);
    if (event) {
      event.is_liked = !event.is_liked;
      event.likes_count += isLiked ? -1 : 1;
    }
  } catch (error) {
    console.error('Failed to toggle like:', error);
  }
};

// View event details
const handleViewEvent = (eventId) => {
  // Navigate to event detail page
  window.location.href = `/events/${eventId}`;
};
</script>

<style scoped>
.recommendations {
  padding: 20px;
}

.loading, .no-recommendations {
  text-align: center;
  padding: 40px;
  color: #666;
}

.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}
</style>
```

### 3. Event Card with Like Button
**File**: `src/components/EventCard.vue`

```vue
<template>
  <div class="event-card">
    <div class="event-image">
      <img :src="event.event_image" :alt="event.name" />
      <button 
        class="like-button"
        :class="{ liked: isLiked }"
        @click="toggleLike"
        :title="isLiked ? 'Unlike' : 'Like'"
      >
        <heart-icon :filled="isLiked" />
      </button>
    </div>

    <div class="event-info">
      <h3 @click="viewEvent" class="event-name">{{ event.name }}</h3>
      
      <div class="event-meta">
        <span class="category">{{ event.category }}</span>
        <span class="club">{{ event.club }}</span>
      </div>

      <p class="event-date">📅 {{ formatDate(event.date) }}</p>
      <p class="event-location">📍 {{ event.location }}</p>

      <div class="likes-section">
        <span class="likes-count">❤️ {{ event.likes_count }}</span>
      </div>

      <button class="view-button" @click="viewEvent">
        View Event
      </button>

      <button 
        v-if="event.is_liked"
        class="similar-button"
        @click="viewSimilar"
      >
        Similar Events
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import HeartIcon from './icons/HeartIcon.vue';

const props = defineProps({
  event: Object,
  isLiked: Boolean
});

const emit = defineEmits(['like', 'view', 'similar']);
const isLiked = ref(props.isLiked);

const toggleLike = async () => {
  emit('like', props.event.id, isLiked.value);
};

const viewEvent = () => {
  emit('view', props.event.id);
};

const viewSimilar = () => {
  emit('similar', props.event.id);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<style scoped>
.event-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.event-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.event-image {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
}

.event-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.like-button {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.like-button:hover {
  background: white;
  transform: scale(1.1);
}

.like-button.liked {
  background: #ff4458;
  color: white;
}

.event-info {
  padding: 16px;
}

.event-name {
  margin: 0 0 8px 0;
  font-size: 18px;
  cursor: pointer;
  color: #333;
  transition: color 0.3s;
}

.event-name:hover {
  color: #ff4458;
}

.event-meta {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.category, .club {
  font-size: 12px;
  background: #f0f0f0;
  padding: 4px 8px;
  border-radius: 4px;
  color: #666;
}

.category {
  background: #e3f2fd;
  color: #1976d2;
}

.event-date, .event-location {
  font-size: 13px;
  color: #666;
  margin: 4px 0;
}

.likes-section {
  margin: 12px 0;
  padding: 8px 0;
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
}

.likes-count {
  font-size: 13px;
  color: #ff4458;
}

.view-button, .similar-button {
  width: 100%;
  padding: 10px;
  margin-top: 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s;
}

.view-button {
  background: #1976d2;
  color: white;
}

.view-button:hover {
  background: #1565c0;
}

.similar-button {
  background: #f0f0f0;
  color: #333;
  border: 1px solid #ddd;
}

.similar-button:hover {
  background: #e8e8e8;
}
</style>
```

### 4. Similar Events Component
**File**: `src/components/SimilarEvents.vue`

```vue
<template>
  <div class="similar-events">
    <h3>Similar Events</h3>
    
    <div v-if="loading" class="loading">Loading...</div>
    <div v-else-if="events.length === 0" class="empty">
      No similar events found
    </div>

    <div v-else class="events-slider">
      <div 
        v-for="event in events"
        :key="event.id"
        class="event-item"
      >
        <img :src="event.event_image" :alt="event.name" />
        <h4>{{ event.name }}</h4>
        <p>{{ event.category }}</p>
        <a href="#" @click="viewEvent(event.id)">View →</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { recommendationService } from '@/services/recommendationService';

const props = defineProps({
  eventId: Number
});

const events = ref([]);
const loading = ref(false);

onMounted(async () => {
  await loadSimilarEvents();
});

const loadSimilarEvents = async () => {
  loading.value = true;
  try {
    const response = await recommendationService.getSimilarEvents(props.eventId, 5);
    events.value = response.data.data;
  } catch (error) {
    console.error('Failed to load similar events:', error);
  } finally {
    loading.value = false;
  }
};

const viewEvent = (eventId) => {
  window.location.href = `/events/${eventId}`;
};
</script>

<style scoped>
.similar-events {
  margin-top: 30px;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
}

.loading, .empty {
  text-align: center;
  color: #666;
  padding: 20px;
}

.events-slider {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  padding: 10px 0;
}

.event-item {
  flex: 0 0 calc(20% - 13px);
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
}

.event-item:hover {
  transform: translateY(-4px);
}

.event-item img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.event-item h4 {
  margin: 8px;
  font-size: 14px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.event-item p {
  margin: 0 8px;
  font-size: 12px;
  color: #666;
}

.event-item a {
  display: block;
  padding: 8px;
  text-align: center;
  color: #1976d2;
  text-decoration: none;
  font-size: 12px;
}

.event-item a:hover {
  background: #f0f0f0;
}
</style>
```

---

## React Integration

### 1. Recommendation Hook
**File**: `src/hooks/useRecommendations.js`

```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

export const useRecommendations = () => {
  const [recommendations, setRecommendations] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const getRecommendations = async (limit = 10) => {
    setLoading(true);
    try {
      const response = await axios.get('/api/recommendations', {
        params: { limit }
      });
      setRecommendations(response.data.data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const likeEvent = async (eventId) => {
    try {
      await axios.post(`/api/events/${eventId}/like`);
      // Update local state
      setRecommendations(prev =>
        prev.map(event =>
          event.id === eventId
            ? { ...event, is_liked: true, likes_count: event.likes_count + 1 }
            : event
        )
      );
    } catch (err) {
      console.error('Failed to like event:', err);
    }
  };

  const unlikeEvent = async (eventId) => {
    try {
      await axios.post(`/api/events/${eventId}/unlike`);
      // Update local state
      setRecommendations(prev =>
        prev.map(event =>
          event.id === eventId
            ? { ...event, is_liked: false, likes_count: event.likes_count - 1 }
            : event
        )
      );
    } catch (err) {
      console.error('Failed to unlike event:', err);
    }
  };

  return {
    recommendations,
    loading,
    error,
    getRecommendations,
    likeEvent,
    unlikeEvent
  };
};
```

### 2. Recommendations Component
**File**: `src/components/Recommendations.jsx`

```jsx
import React, { useEffect } from 'react';
import { useRecommendations } from '@/hooks/useRecommendations';
import EventCard from './EventCard';

export const Recommendations = () => {
  const { recommendations, loading, getRecommendations, likeEvent, unlikeEvent } = useRecommendations();

  useEffect(() => {
    getRecommendations(10);
  }, []);

  const handleLike = (eventId, isLiked) => {
    isLiked ? unlikeEvent(eventId) : likeEvent(eventId);
  };

  if (loading) return <div className="loading">Loading recommendations...</div>;

  return (
    <div className="recommendations">
      <h2>Recommended For You</h2>
      <div className="events-grid">
        {recommendations.map(event => (
          <EventCard
            key={event.id}
            event={event}
            onLike={handleLike}
          />
        ))}
      </div>
    </div>
  );
};

export default Recommendations;
```

---

## Vanilla JavaScript Implementation

### 1. Simple Like Button
**File**: `public/js/like-button.js`

```javascript
class EventLikeButton {
  constructor(containerId, eventId) {
    this.container = document.getElementById(containerId);
    this.eventId = eventId;
    this.token = localStorage.getItem('auth_token');
    this.isLiked = false;
    this.init();
  }

  async init() {
    // Create button
    this.button = document.createElement('button');
    this.button.className = 'like-button';
    this.button.innerHTML = '❤️ Like';
    this.button.addEventListener('click', () => this.toggleLike());

    // Check current status
    await this.checkStatus();

    this.container.appendChild(this.button);
  }

  async checkStatus() {
    try {
      const response = await fetch(`/api/events/${this.eventId}/like-status`, {
        headers: { 'Authorization': `Bearer ${this.token}` }
      });
      const data = await response.json();
      this.isLiked = data.data.is_liked_by_user;
      this.updateButton();
    } catch (error) {
      console.error('Failed to check like status:', error);
    }
  }

  async toggleLike() {
    const endpoint = this.isLiked ? 'unlike' : 'like';
    
    try {
      const response = await fetch(`/api/events/${this.eventId}/${endpoint}`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${this.token}` }
      });
      
      if (response.ok) {
        this.isLiked = !this.isLiked;
        this.updateButton();
      }
    } catch (error) {
      console.error('Failed to toggle like:', error);
    }
  }

  updateButton() {
    this.button.textContent = this.isLiked ? '❤️ Liked' : '❤️ Like';
    this.button.classList.toggle('liked', this.isLiked);
  }
}

// Usage: new EventLikeButton('like-container', eventId);
```

### 2. Load Recommendations
**File**: `public/js/recommendations.js`

```javascript
class RecommendationLoader {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.token = localStorage.getItem('auth_token');
  }

  async loadRecommendations(limit = 10) {
    try {
      this.container.innerHTML = '<p>Loading...</p>';
      
      const response = await fetch(`/api/recommendations?limit=${limit}`, {
        headers: { 'Authorization': `Bearer ${this.token}` }
      });
      
      const data = await response.json();
      this.renderEvents(data.data);
    } catch (error) {
      this.container.innerHTML = `<p>Error loading recommendations: ${error.message}</p>`;
    }
  }

  renderEvents(events) {
    this.container.innerHTML = '';
    
    const grid = document.createElement('div');
    grid.className = 'events-grid';
    
    events.forEach(event => {
      const card = this.createEventCard(event);
      grid.appendChild(card);
    });
    
    this.container.appendChild(grid);
  }

  createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-card';
    card.innerHTML = `
      <img src="${event.event_image}" alt="${event.name}" />
      <div class="event-info">
        <h3>${event.name}</h3>
        <p class="category">${event.category}</p>
        <p class="club">${event.club}</p>
        <p class="date">📅 ${event.date}</p>
        <p class="location">📍 ${event.location}</p>
        <p class="likes">❤️ ${event.likes_count}</p>
        <button class="view-button" onclick="window.location='/events/${event.id}'">
          View Event
        </button>
      </div>
    `;
    return card;
  }
}

// Usage: new RecommendationLoader('recommendations-container').loadRecommendations(10);
```

### 3. HTML Integration
**File**: `public/event-detail.html`

```html
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div id="event-details">
    <!-- Event content -->
  </div>

  <!-- Like Button -->
  <div id="like-container"></div>

  <!-- Similar Events -->
  <div id="similar-events"></div>

  <script src="/js/like-button.js"></script>
  <script src="/js/recommendations.js"></script>
  <script>
    const eventId = new URLSearchParams(window.location.search).get('id');
    
    // Add like button
    new EventLikeButton('like-container', eventId);
    
    // Load similar events
    const similarLoader = new RecommendationLoader('similar-events');
    fetch(`/api/recommendations/similar/${eventId}?limit=5`, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    })
    .then(r => r.json())
    .then(data => similarLoader.renderEvents(data.data));
  </script>
</body>
</html>
```

---

## Common UI Patterns

### Like Button Styling
```css
.like-button {
  background: white;
  border: 2px solid #ddd;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  cursor: pointer;
  font-size: 24px;
  transition: all 0.3s;
}

.like-button:hover {
  border-color: #ff4458;
  transform: scale(1.1);
}

.like-button.liked {
  background: #ff4458;
  border-color: #ff4458;
  color: white;
}
```

### Recommendations Grid
```css
.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  padding: 20px;
}

.event-card {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.event-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
```

---

## Error Handling

```javascript
const handleError = (error, defaultMessage = 'Something went wrong') => {
  if (error.response?.status === 401) {
    // Redirect to login
    window.location.href = '/login';
  } else if (error.response?.status === 409) {
    // Event already liked
    console.warn('Event already liked');
  } else {
    console.error(defaultMessage, error);
  }
};
```

---

## Next Steps

1. Choose your framework (Vue, React, or Vanilla JS)
2. Copy the relevant code from above
3. Install axios if needed: `npm install axios`
4. Integrate into your UI components
5. Test with the API endpoints
6. Customize styling to match your design

Happy coding! 🚀
