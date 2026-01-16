<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

interface Profile {
    id: number;
    name: string;
    biography: string;
    is_online: boolean;
    photos_count: number;
    is_within_online_hours: boolean;
    videos_count: number;
    likes_count: number;
    action_label: string;
    script_url: string | null;
    banner_url: string | null;
    avatar_url: string | null;
    logo_url: string | null;
    certification_url: string | null;
}

interface Post {
    id: number;
    content: string | null;
    type: 'tout' | 'live' | 'rencontre';
    duration?: string | null;
    likes_count: number;
    is_visible: boolean;
    is_blurred: boolean;
    is_live: boolean;
    created_at: string;
    media: Array<{
        id: number;
        url: string;
        type: string;
    }>;
}

const props = defineProps<{
    profile: Profile;
    posts: Post[];
}>();

const activeTab = ref<'tout' | 'live' | 'rencontre'>('tout');
const displayMode = ref<'list' | 'grid'>('list');
const showStickyBar = ref(false);
const actionBarRef = ref<HTMLElement | null>(null);
const actionBarTop = ref(0);

const filteredPosts = computed(() => {
    return props.posts.filter(post => {
        if (activeTab.value === 'tout') {
            return post.type === 'photo' || post.type === 'video';
        } else if (activeTab.value === 'live') {
            return post.type === 'live';
        } else if (activeTab.value === 'rencontre') {
            return post.type === 'rencontre';
        }
    });
});

// Check if one post is type live
const hasLivePost = computed(() => props.posts.some(post => post.type === 'live'));

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    const time = date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
    });

    if (days === 0) {
        return `Aujourd’hui à ${time}`;
    } else if (days === 1) {
        return `Hier à ${time}`;
    } else if (days < 7) {
        return `Il y a ${days} jours à ${time}`;
    } else {
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }) + ` à ${time}`;
    }
};

const isLive = () => {
    return props.posts.some(post => post.is_live) || props.profile.is_within_online_hours;
};

const isOnline = () => {
    return props.profile.is_online || props.profile.is_within_online_hours || isLive();
};


// Gestion du scroll pour la sticky bar
const handleScroll = () => {
    if (actionBarRef.value) {
        const rect = actionBarRef.value.getBoundingClientRect();
        // Si la barre d'action originale est au-dessus du viewport
        showStickyBar.value = rect.bottom < 0;
    }
};

let ptScriptEl: HTMLScriptElement | null = null;

function loadExternalScript(src: string): Promise<void> {
    
    return new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[data-ptprelinker="true"][src="${src}"]`) as HTMLScriptElement | null;
        if (existing) return resolve();

        const s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.defer = true;
        s.setAttribute('data-ptprelinker', 'true');

        s.onload = () => resolve();
        s.onerror = () => reject(new Error(`Impossible de charger le script: ${src}`));

        document.head.appendChild(s);
        ptScriptEl = s;
    });
}

onMounted(async () => {
    window.addEventListener('scroll', handleScroll);
    if (actionBarRef.value) {
        actionBarTop.value = actionBarRef.value.offsetTop;
    }

    if (props.profile.script_url) {
        try {
            await loadExternalScript(props.profile.script_url);
        } catch (e) {
            console.error(e);
        }
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);

    if (ptScriptEl?.parentNode) {
        ptScriptEl.parentNode.removeChild(ptScriptEl);
        ptScriptEl = null;
    }
});
</script>

<template>
    <Head :title="profile.name">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <div class="min-h-screen bg-black">
        <!-- Container centré avec bordures -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Banner Section -->
            <div class="relative w-full h-48 sm:h-56 md:h-64 bg-gray-900 rounded-t-lg overflow-hidden" :style="profile.banner_url ? { backgroundImage: `url(${profile.banner_url})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}">
                <!-- Overlay gradient -->
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/60"></div>

                <!-- Name en haut à gauche -->
                <div class="absolute top-1 left-3 sm:top-2 sm:left-4 z-10">
                    <div class="flex items-center gap-2 mb-1 sm:mb-2">
                        <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-white">{{ profile.name }}</h2>
                    </div>
                </div>

                <!-- en haut au centre : logo -->
                <div class="absolute top-1 left-1/2 z-10 -translate-x-1/2 sm:top-2">
                    <div class=" bg-transparent px-3 py-2">
                        <img
                            v-if="profile.logo_url"
                            :src="profile.logo_url"
                            alt="Logo"
                            class="h-8 w-auto object-contain sm:h-10"
                            loading="lazy"
                        />
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="bg-black border-x border-b border-gray-800 rounded-b-lg p-4 sm:p-6 lg:p-8 pb-24 sm:pb-28">
                <!-- Profile Header + Biography with avatar inside the card -->
                <div class="mb-6 relative bg-gray-900/40 rounded-lg p-4 sm:p-5 lg:p-6">
                    <!-- Avatar -->
                    <div class="absolute -top-10 sm:-top-12 left-0 sm:left-0">
                        
                        <!-- Live ring: sur le wrapper (pas clipé) -->
                        <span
                        v-if="isLive() || isOnline()"
                        class="absolute -inset-[4px] rounded-full
                                bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                live-pulse pointer-events-none z-0"
                        aria-hidden="true"
                        />

                        <!-- Avatar -->
                        <div
                            class="relative z-10 w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28
                                rounded-full border-4 border-black bg-gray-800 overflow-hidden"
                        >
                            <img
                            v-if="profile.avatar_url"
                            :src="profile.avatar_url"
                            :alt="profile.name"
                            class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-10 h-10 sm:w-12 sm:h-12" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            </div>
                        </div>

                        <!-- ✅ Online dot -->
                        <span
                            v-if="isOnline()"
                            class="absolute bottom-1 right-1 sm:bottom-1.5 sm:right-1.5
                                w-3 h-3 sm:w-4 sm:h-4
                                rounded-full bg-green-500
                                ring-4 ring-black z-30"
                            title="En ligne"
                        />

                        <!-- Live badge: sur le wrapper (donc visible) -->
                        <span
                            v-if="isLive()"
                            class="absolute -bottom-4 left-1/2 -translate-x-1/2
                                bg-red-500 text-white
                                px-2 py-0.5
                                rounded-full
                                text-xs sm:text-sm font-bold
                                border border-red-300/60
                                shadow-sm
                                live-pulse z-20"
                        >
                            LIVE
                        </span>
                    </div>


                                 <!-- Stats en haut à droite -->
                    <div class="absolute top-3 right-3 sm:top-4 sm:right-4 z-10">
                            <div class="flex items-center gap-3 sm:gap-4 text-white text-xs sm:text-sm md:text-base">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ profile.photos_count }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                    </svg>
                                    <span>{{ profile.videos_count }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ profile.likes_count }}</span>
                                </div>
                            </div>
                    </div>

                    <!-- ✅ Contenu en dessous de l’avatar -->
                    <div class="pt-14 sm:pt-16 md:pt-16">
                        <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white">
                            {{ profile.name }}
                        </h1>

   
                    <!-- icon certification -->
                    <img
                        v-if="profile.certification_url"
                        :src="profile.certification_url"
                        :alt="profile.name"
                        class="w-6 h-6 object-cover"
                    />
            
                        <div v-if="isOnline()" class="text-sm text-white flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            En ligne
                        </div>
                    </div>

                        <p class="text-xs sm:text-sm text-gray-300 mb-2 flex items-center gap-1">
                        <!-- icon -->
                         <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        à proximité 12,4km
                        </p>

                        <p v-if="profile.biography" class="text-white font-bold mb-2 text-sm sm:text-base">
                        {{ profile.biography }}
                        </p>
                    </div>
                </div>

                <!-- Action Bar (original position) -->
                <div
                    ref="actionBarRef"
                    class="relative bg-gray-900 border border-gray-800 rounded-lg mb-6
                            flex items-start"
                    >
                    <!-- Avatar: top-left corner -->
                    <div
                    class="absolute -top-3 -left-2
                        w-8 h-8 sm:w-10 sm:h-10
                        rounded-full bg-gray-800 overflow-hidden"
                    >
                        <img
                        v-if="profile.avatar_url"
                        :src="profile.avatar_url"
                        :alt="profile.name"
                        class="w-full h-full object-cover"
                        />
                    </div>

                    <!-- Content (space reserved for avatar) -->
                    <p class="text-white font-bold flex-1 text-sm sm:text-base pl-10 sm:pl-10 p-3 sm:p-4">
                        J'aime ceux qui osent. 💋
                    </p>
                </div>


                <!-- Subscribe Button -->
                <a
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block w-full text-center
                            bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                            hover:from-pink-600 hover:via-rose-600 hover:to-orange-500
                            text-white font-semibold
                            py-3 px-6 rounded-lg mb-6
                            transition-all duration-200
                            shadow-md hover:shadow-lg
                            text-sm sm:text-base"
                    >
                    {{ profile.action_label || "S'abonner gratuitement" }}
                    </a>


                <!-- Tabs -->
                <div class="flex items-center justify-between mb-6 border-b border-gray-800 pb-2">
                    <!-- Tabs (style pill comme Display Mode Toggle) -->
                    <div class="flex gap-1 bg-gray-900 rounded-lg p-1">
                        <button
                            @click="activeTab = 'tout'"
                            :class="[
                                'px-3 py-2 rounded font-medium transition-colors text-sm sm:text-base',
                                activeTab === 'tout'
                                ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 text-white shadow-sm'
                                : 'text-gray-400 hover:text-white'
                            ]"
                        >
                            Tout
                        </button>

                        <button
                            @click="activeTab = 'live'"
                            class="relative px-3 py-2 rounded font-medium transition-colors text-sm sm:text-base"
                            :class="activeTab === 'live'
                                ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 text-white shadow-sm'
                                : hasLivePost && isLive() ? 'text-transparent bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 bg-clip-text font-semibold' : 'text-gray-400 hover:text-white'"
                        >
                            Live

                            <!-- live dot (si live existe) -->
                            <span
                                v-if="hasLivePost && activeTab !== 'live' && isLive()"
                                class="absolute top-1 right-0 h-2 w-2 rounded-full bg-red-600 animate-pulse"
                            />
                        </button>

                        <button
                            @click="activeTab = 'rencontre'"
                            :class="[
                                'px-3 py-2 rounded font-medium transition-colors text-sm sm:text-base',
                                activeTab === 'rencontre'
                                ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 text-white shadow-sm'
                                    : 'text-gray-400 hover:text-white'
                            ]"
                        >
                            Rencontre
                        </button>

                    </div>

                    <!-- Display Mode Toggle -->
                    <div class="flex gap-1 bg-gray-900 rounded-lg p-1">
                        <button 
                            @click="displayMode = 'list'"
                            :class="[
                                'p-2 rounded transition-colors',
                                displayMode === 'list' 
                                    ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 text-white shadow-sm'
                                    : 'text-gray-400 hover:text-white'
                            ]"
                            title="Vue liste"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button 
                            @click="displayMode = 'grid'"
                            :class="[
                                'p-2 rounded transition-colors',
                                displayMode === 'grid' 
                                    ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 text-white shadow-sm'
                                    : 'text-gray-400 hover:text-white'
                            ]"
                            title="Vue grille"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Posts Feed - List View -->
                <div v-if="displayMode === 'list'" class="space-y-4 sm:space-y-6">
                    <div 
                        v-for="post in filteredPosts" 
                        :key="post.id"
                        class="bg-black border border-gray-800 rounded-lg p-4 sm:p-6"
                    >
                        <!-- Post Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="relative flex-shrink-0">
                                    <!-- ✅ live ring en dégradé (derrière l’avatar) -->
                                    <span
                                    v-if="isLive() || isOnline()"
                                    class="absolute -inset-[2px] rounded-full
                                            bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                            animate-pulse z-0"
                                    aria-hidden="true"
                                    />

                                    <!-- avatar -->
                                    <div
                                    class="relative z-10 w-8 h-8 sm:w-10 sm:h-10
                                            rounded-full overflow-hidden bg-gray-800"
                                    >
                                    <a href="#" target="_blank" rel="noopener noreferrer">
                                        <img
                                            v-if="profile.avatar_url"
                                            :src="profile.avatar_url"
                                            :alt="profile.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </a>
                                    </div>
                                </div>


                                <div>
                                    <div class="flex items-center gap-1 sm:gap-2 flex-wrap">
                                        <span class="font-semibold text-white text-sm sm:text-base">
                                            {{ profile.name }}
                                        </span>
                                        <span
                                            v-if="isLive() && post.type === 'live'"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full
                                                    text-[10px] sm:text-xs font-bold
                                                    bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                                    text-white ml-1 sm:ml-2
                                                    animate-pulse"
                                            >
                                            LIVE
                                            </span>

                                        <span v-if="isOnline()" class="text-sm text-white flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            En ligne
                                </span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs sm:text-sm text-gray-400 whitespace-nowrap ml-2">
                                {{ formatDate(post.created_at) }}
                            </span>
                        </div>

                        <!-- Post Content -->
                        <p v-if="post.content" class="text-white mb-4 text-sm sm:text-base">
                            {{ post.content }}
                        </p>

                        <!-- Post Media -->
                        <div v-if="post.media && post.media.length > 0" class="mb-4">
                            <div 
                                v-for="(media, index) in post.media" 
                                :key="index"
                                class="relative rounded-lg overflow-hidden bg-gray-900 mb-2 select-none"
                                @contextmenu.prevent
                            >
                                <!-- Image -->
                                <img 
                                    v-if="media.type.startsWith('image')"
                                    :src="media.url"
                                    :alt="`Media ${index + 1}`"
                                    class="w-full h-auto object-cover pointer-events-none"
                                    draggable="false"
                                    oncontextmenu="return false;"
                                />
                                <div v-else class="aspect-video bg-gray-900 flex items-center justify-center">
                                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                                    </svg>
                                </div>
                                
                                <!-- LIVE Badge -->
                                    <div
                                    v-if="isLive() && post.type === 'live'"
                                    class="absolute top-3 left-3 z-40
                                            bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                            text-white px-3 py-1 rounded-full
                                            text-xs sm:text-sm font-bold flex items-center gap-1.5
                                            animate-pulse shadow-md"
                                    >
                                    <span class="w-2 h-2 bg-white rounded-full"></span>
                                    LIVE
                                    </div>


                                <!-- Video duration (top-right) -->
                                <div 
                                    v-if="post.duration && (post.type === 'video' || post.type === 'live')"
                                    class="absolute z-40 top-3 right-3 z-30 bg-black/70 text-white px-2 py-0.5 rounded text-xs sm:text-sm font-semibold"
                                >
                                    {{ post.duration }}
                                </div>
                                
                                <!-- Overlay de protection (empêche le drag) -->
                                <div 
                                    class="absolute inset-0 z-10"
                                    @contextmenu.prevent
                                    @dragstart.prevent
                                ></div>
                                
                                <!-- Lock Overlay avec photo de profil (uniquement si flouté) -->
                                <a
                                v-if="post.is_blurred"
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="absolute inset-0 z-30
                                        bg-black/35 backdrop-blur-md
                                        flex items-center justify-center cursor-pointer"
                                >
                                <div class="text-center px-6">
                                    <!-- Avatar + ring + lock badge -->
                                    <div class="relative mx-auto w-20 h-20 sm:w-24 sm:h-24 rounded-full">
                                        <!-- ring rouge -->
                                        <span
                                            v-if="isLive() || isOnline()"
                                            class="absolute -inset-[4px] rounded-full
                                                    bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                                    animate-pulse z-0"
                                            aria-hidden="true"
                                            />

                                        <!-- avatar -->
                                        <div class="relative z-10 w-full h-full rounded-full overflow-hidden bg-gray-800">
                                            <img
                                            v-if="profile.avatar_url"
                                            :src="profile.avatar_url"
                                            :alt="profile.name"
                                            class="w-full h-full object-cover"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                            </div>
                                        </div>

                                        <!-- lock badge (en bas à droite) -->
                                        <div
                                            class="absolute -bottom-2 -right-2
                                                w-10 h-10 rounded-full bg-black
                                                flex items-center justify-center
                                                ring-4 ring-black z-10"
                                        >
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Name + verified -->
                                    <div class="mt-4 flex items-center justify-center gap-2">
                                    <p class="text-white text-2xl sm:text-3xl font-extrabold tracking-tight">
                                        {{ profile.name }}
                                    </p>

                                    <!-- icon certification -->
                                    <img
                                        v-if="profile.certification_url"
                                        :src="profile.certification_url"
                                        :alt="profile.name"
                                        class="w-6 h-6 object-cover"
                                    />

                                    </div>

                                    <!-- CTA button (dégradé pill) -->
                                    <span
                                    class="mt-6 px-10 py-3 rounded-full text-white font-bold text-lg
                                        shadow-lg transition-all duration-200
                                        bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                        hover:from-pink-600 hover:via-rose-600 hover:to-orange-500
                                        inline-block"
                                    >
                                    {{ post.is_live && (post.type === 'video' || post.type === 'live') ? 'Accéder au live' : 'Débloquer' }}
                                    </span>
                                </div>
                                </a>

                            </div>
                        </div>

                        <!-- Post Footer -->
                        <div class="flex items-center gap-4 text-gray-400">
                            <button class="flex items-center gap-2 hover:text-[#8B0000] transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                <span class="text-sm sm:text-base">{{ post.likes_count }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Posts Feed - Grid View -->
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 sm:gap-3">
                    <div 
                        v-for="post in filteredPosts" 
                        :key="post.id"
                        class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 select-none group cursor-pointer"
                        @contextmenu.prevent
                    >
                        <!-- First Media of Post -->
                        <template v-if="post.media && post.media.length > 0">
                            <img 
                                v-if="post.media[0].type.startsWith('image')"
                                :src="post.media[0].url"
                                :alt="`Post ${post.id}`"
                                class="w-full h-full object-cover pointer-events-none"
                                draggable="false"
                                oncontextmenu="return false;"
                            />
                            <div v-else class="w-full h-full bg-gray-900 flex items-center justify-center">
                                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                                </svg>
                            </div>
                        </template>
                        
                        <!-- LIVE Badge -->
                        <div 
                            v-if="post.is_live && (post.type === 'video' || post.type === 'live')"
                            class="absolute top-2 left-2 z-30
                                    bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                    text-white px-2 py-0.5 rounded
                                    text-xs font-bold flex items-center gap-1
                                    animate-pulse shadow-md"
                            >
                            <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                            LIVE
                        </div>


                        <!-- Video duration (top-right) -->
                        <div 
                            v-if="post.duration && (post.type === 'video' || post.type === 'live')"
                            class="absolute top-2 right-2 z-30 bg-black/70 text-white px-1.5 py-0.5 rounded text-[10px] sm:text-xs font-semibold flex items-center gap-1"
                        >
                            {{ post.duration }}
                        </div>
                        
                        <!-- Multiple media indicator -->
                        <div 
                            v-if="post.media && post.media.length > 1"
                            class="absolute top-2 right-2 z-30 bg-black/60 text-white px-2 py-0.5 rounded text-xs font-medium"
                        >
                            +{{ post.media.length - 1 }}
                        </div>
                        
                        <!-- Overlay de protection -->
                        <div 
                            class="absolute inset-0 z-10"
                            @contextmenu.prevent
                            @dragstart.prevent
                        ></div>
                        
                        <!-- Lock Overlay (uniquement si flouté) hover display svg lock -->
                        <a 
                            v-if="post.is_blurred"
                            href="#"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="absolute inset-0 z-20 bg-black/40 flex items-center justify-center cursor-pointer"
                        >
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/60 flex items-center justify-center border border-white/20 group-hover:hidden">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </a>
                        
                        <!-- Hover overlay with likes -->
                        <div class="absolute inset-0 z-25 bg-black/0 group-hover:bg-black/50 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="flex items-center gap-2 text-white">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                </svg>
                                <span class="font-semibold">{{ post.likes_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredPosts.length === 0" class="text-center py-12">
                    <p class="text-gray-400">
                        Aucune publication pour le moment.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Sticky Action Bar (appears when scrolling down) -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-300 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
                <div 
                    v-if="showStickyBar"
                    class="fixed bottom-4 left-1/2 -translate-x-1/2
                        w-[calc(100%-2rem)] md:w-6/12
                        z-50
                        bg-gray-900/95 backdrop-blur-sm
                        border border-gray-800
                        rounded-xl
                        p-3 sm:p-4
                        shadow-lg"
                >

                <div class="max-w-6xl mx-auto flex items-center gap-3">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full
                                bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                p-[2px] flex-shrink-0"
                        >
                            <a href="#" target="_blank" rel="noopener noreferrer">
                                <div class="w-full h-full rounded-full bg-gray-800 overflow-hidden">
                                    <img 
                                    v-if="profile.avatar_url"
                                    :src="profile.avatar_url"
                                    :alt="profile.name"
                                    class="w-full h-full object-cover"
                                    />
                                </div>
                            </a>
                        </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium text-sm sm:text-base truncate">
                            {{ profile.name }}
                        </p>
                        <p class="text-gray-400 text-xs sm:text-sm truncate">
                            J'aime ceux qui osent. 💋
                        </p>
                    </div>
                    <a 
                        href="#"
                        rel="noopener noreferrer"
                        class="bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400
                                hover:from-pink-600 hover:via-rose-600 hover:to-orange-500
                                text-white font-semibold
                                py-2 px-4 sm:py-2.5 sm:px-6
                                rounded-full
                                transition-all duration-200
                                shadow-md hover:shadow-lg
                                text-sm sm:text-base whitespace-nowrap"
                        >
                        {{ profile.action_label || "S'abonner au VIP" }}
                        </a>

                </div>
        </div>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes softBlink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

@keyframes livePulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.65;
  }
}

.live-pulse {
  animation: livePulse 1.6s ease-in-out infinite;
}
</style>

    