<template>
  <div class="main-container">
    <!-- Navbar -->
    <NavBar />

    <!-- Main Content -->
    <div class="main-content">
      <div class="content-section">
        <div class="content-area">
          <!-- Integrated Header -->
          <div class="header-row">
            <div class="title-and-create">
              <h2 class="section-title">{{ currentTabTitle }}</h2>
              <button v-if="isAuthenticated" @click="showUploadForm = true" class="create-button">Crear</button>
            </div>
            <div class="tabs-inline">
              <router-link v-for="tab in tabs" :key="tab.path" :to="tab.path" class="tab" :class="{ active: $route.path === tab.path }">
                {{ tab.name }}
              </router-link>
            </div>
          </div>
          <div class="action-row">
            <div class="filter-buttons">
              <button class="filter-btn" @click="sortBy = 'recent'">Más Recientes</button>
              <button class="filter-btn" @click="sortBy = 'popular'">Más Populares</button>
            </div>
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
    ContentList: defineAsyncComponent(() => import('@/components/ContentList.vue')),
    UploadForm: defineAsyncComponent(() => import('@/components/UploadForm.vue')),
    ContentModal: defineAsyncComponent(() => import('@/components/ContentModal.vue')),
  },
  setup() {
    const { isAuthenticated, userTier, ...rest } = CommunityViewLogic();
    return {
      isAuthenticated,
      userTier,
      sortBy: 'recent',
      ...rest,
    };
  },
  computed: {
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
      if (this.$route.path === '/comunidad') content = this.allContent;
      else if (this.$route.path === '/comunidad/capturas') content = this.sortedScreenshots;
      else if (this.$route.path === '/comunidad/guias') content = this.sortedGuides;
      else if (this.$route.path === '/videos') content = this.sortedVideos;
      else if (this.$route.path === '/retransmisiones') content = [];

      return this.sortBy === 'popular' ? content.sort((a, b) => b.likes - a.likes) : content.sort((a, b) => new Date(b.date) - new Date(a.date));
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
    handleContentClick(item) {
      if (this.$route.path === '/comunidad/capturas') this.openScreenshotModal(item);
      else if (this.$route.path === '/comunidad/guias') this.viewGuide(item);
      else if (this.$route.path === '/videos') this.openVideoModal(item);
    },
  },
};
</script>

<style src="@/assets/styles/CommunityView.css" scoped></style>