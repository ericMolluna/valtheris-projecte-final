<template>
  <div class="main-container">
    <!-- Navbar -->
    <NavBar />

    <!-- Tabs Container -->
    <div class="tabs-container">
      <router-link v-for="tab in tabs" :key="tab.path" :to="tab.path" class="tab" :class="{ active: $route.path === tab.path }">
        {{ tab.name }}
      </router-link>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="content-section">
        <div v-if="$route.path === '/comunidad' || $route.path === '/comunidad/capturas' || $route.path === '/comunidad/guias' || $route.path === '/videos' || $route.path === '/retransmisiones'" class="content-area">
          <ContentHeader 
            :title="currentTabTitle" 
            :showCreate="isAuthenticated" 
            @create-click="handleCreateClick" 
          />
          
          <!-- Filter Buttons -->
          <div class="filter-buttons" v-if="$route.path === '/comunidad/capturas' || $route.path === '/comunidad/guias' || $route.path === '/videos'">
            <button class="filter-btn" @click="sortBy = 'recent'">Más Recientes</button>
            <button class="filter-btn" @click="sortBy = 'popular'">Más Populares</button>
          </div>

          <!-- Content List -->
          <ContentList
            :items="filteredContent"
            :loading="loadingContent"
            :errorMessage="errorMessageContent"
            @item-click="handleContentClick"
          />
          <template v-if="!loadingContent && !filteredContent.length">
            <p class="no-content-message">No hay {{ currentTabTitle.toLowerCase() }} disponibles todavía.</p>
          </template>
        </div>
      </div>
    </div>

    <!-- Upload Forms -->
    <UploadForm
      v-if="showUploadForm"
      type="screenshot"
      v-model="newScreenshot"
      :error="uploadError"
      @close="showUploadForm = false"
      @upload="uploadScreenshot"
    />
    <UploadForm
      v-if="showVideoUploadForm"
      type="video"
      v-model="newVideo"
      :error="videoUploadError"
      @close="showVideoUploadForm = false"
      @upload="uploadVideo"
    />

    <!-- Modals -->
    <ContentModal
      v-if="selectedScreenshot"
      type="screenshot"
      :item="selectedScreenshot"
      :comments="comments"
      :isAuthenticated="isAuthenticated"
      v-model:comment="newComment"
      @close="closeScreenshotModal"
      @submit-comment="submitComment"
      @delete="deleteScreenshot"
      @toggle-like="toggleLike"
      @toggle-dislike="toggleDislike"
    />
    <ContentModal
      v-if="selectedVideo"
      type="video"
      :item="selectedVideo"
      :comments="videoComments"
      :isAuthenticated="isAuthenticated"
      v-model:comment="newVideoComment"
      @close="closeVideoModal"
      @submit-comment="submitVideoComment"
    />

    <!-- Footer -->
    <FooterSection />
  </div>
</template>

<script>
import { defineAsyncComponent } from 'vue';
import CommunityViewLogic from './CommunityView.js';

export default {
  name: 'CommunityView',
  components: {
    NavBar: defineAsyncComponent(() => import('@/components/NavBar.vue')),
    FooterSection: defineAsyncComponent(() => import('@/components/FooterSection.vue')),
    ContentHeader: defineAsyncComponent(() => import('@/components/ContentHeader.vue')),
    ContentList: defineAsyncComponent(() => import('@/components/ContentList.vue')),
    UploadForm: defineAsyncComponent(() => import('@/components/UploadForm.vue')),
    ContentModal: defineAsyncComponent(() => import('@/components/ContentModal.vue')),
  },
  setup() {
    const { isAuthenticated, userTier, ...rest } = CommunityViewLogic();
    return {
      isAuthenticated,
      userTier,
      sortBy: 'recent', // Default sorting
      ...rest,
    };
  },
  computed: {
    tabs() {
      return [
        { name: 'Todo', path: '/comunidad' },
        { name: 'Capturas', path: '/comunidad/capturas' },
        { name: 'Guías', path: '/comunidad/guias' },
        { name: 'Videos', path: '/videos' },
        { name: 'Retransmisiones', path: '/retransmisiones' },
      ];
    },
    currentTabTitle() {
      const path = this.$route.path;
      if (path === '/comunidad') return 'Todo';
      if (path === '/comunidad/capturas') return 'Capturas';
      if (path === '/comunidad/guias') return 'Guías';
      if (path === '/videos') return 'Videos';
      if (path === '/retransmisiones') return 'Retransmisiones';
      return 'Todo';
    },
    filteredContent() {
      let content = [];
      if (this.$route.path === '/comunidad') content = this.allContent || [];
      else if (this.$route.path === '/comunidad/capturas') content = this.sortedScreenshots || [];
      else if (this.$route.path === '/comunidad/guias') content = this.sortedGuides || [];
      else if (this.$route.path === '/videos') content = this.sortedVideos || [];
      else if (this.$route.path === '/retransmisiones') content = [];
      return this.sortBy === 'popular' ? content.sort((a, b) => (b.likes || 0) - (a.likes || 0)) : content.sort((a, b) => new Date(b.date || 0) - new Date(a.date || 0));
    },
    loadingContent() {
      if (this.$route.path === '/comunidad') return this.loadingAllContent;
      else if (this.$route.path === '/comunidad/capturas') return this.loadingScreenshots;
      else if (this.$route.path === '/comunidad/guias') return this.loadingGuides;
      else if (this.$route.path === '/videos') return this.loadingVideos;
      return false;
    },
    errorMessageContent() {
      if (this.$route.path === '/comunidad') return this.errorMessageAllContent;
      else if (this.$route.path === '/comunidad/capturas') return this.errorMessageScreenshots;
      else if (this.$route.path === '/comunidad/guias') return this.errorMessageGuides;
      else if (this.$route.path === '/videos') return this.errorMessageVideos;
      return '';
    },
  },
  methods: {
    handleCreateClick() {
      if (this.$route.path === '/comunidad/guias') {
        this.$router.push('/comunidad/crear-guia');
      } else if (this.$route.path === '/comunidad/capturas') {
        this.showUploadForm = true;
      } else if (this.$route.path === '/videos') {
        this.showVideoUploadForm = true;
      }
    },
    handleContentClick(item) {
      if (this.$route.path === '/comunidad/capturas') {
        this.openScreenshotModal(item);
      } else if (this.$route.path === '/comunidad/guias') {
        this.viewGuide(item);
      } else if (this.$route.path === '/videos') {
        this.openVideoModal(item);
      }
    },
    handleCreateClick() {
      if (this.$route.path === '/comunidad/guias') {
        // Navigate to CreateGuide view
        this.$router.push({ name: 'CreateGuide' });
      } else if (this.$route.path === '/comunidad/capturas') {
        // Show screenshot upload form
        this.showUploadForm = true;
      } else if (this.$route.path === '/videos') {
        // Show video upload form
        this.showVideoUploadForm = true;
      }
    },
  },
};
</script>

<style src="@/assets/styles/CommunityView.css" scoped></style>