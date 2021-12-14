/* Handle files uploads */

import { make } from 'vuex-pathify'
import { store } from '@/config'

import FilesApi from "@/services/api/Files";
import UploadFileObject from "@/classes/upload-file-object";
import UploadsApi from '@/services/api/Uploads';

const defaultServerStatus = {
  maxPostSize: 0
};

const state = () => {
  return {
    alwaysUseChunks: false,
    autoUpload: false,
    files: [],
    maxChunkRetries: 2,
    serverStatus: Object.simpleClone(defaultServerStatus),
    uploading: false
  }
};

const actions = {
  clearUploads ({ commit }) {
    commit('CLEAR');
  },
  deleteUploads ({ state }) {
    let fileIds = state.files.map( f => f.id );

    return FilesApi.deleteFiles({ fileIds })
      .then( response => {

      })
      .catch(() => {});
  },
  fileDelete ({ commit }, file) {
    if (file && file.id) {
      FilesApi.delete(file.id)
        .then(() => {
          commit('DELETE_FILE', file);
        })
        .catch(() => {});
    }
  },
  fileAdd ({ commit, dispatch, getters, state }, { fileSource, fileOptions }) {
    return new Promise( (resolve, reject ) => {
      if ((typeof fileSource === 'object') && (fileSource.constructor.name === 'File')) {
        let options = Object.assign(
          {},
          {
            maxChunkRetries: state.maxChunkRetries,
            maxPostSize: getters['maxPostSize'],
            onRejectedDispatch: 'uploadNextOnAuto',
            onUploadedDispatch: 'uploadNextOnAuto',
            storeNamespace: store.namespace.uploads
          },
          fileOptions || {}
        );
        let file = new UploadFileObject(fileSource, options);

        commit('APPEND_FILE', file);
        dispatch('uploadNextOnAuto');

        resolve (file);
      }

      reject(new Error('Source must be an object of type File'));
    });
  },
  filesAdd ({ dispatch }, { fileList, fileOptions }) {
    return new Promise( resolve => {
      if ((typeof fileList === 'object') && (fileList.constructor.name === 'FileList')) {
        ([...fileList]).forEach( f => {
          dispatch('fileAdd', { fileSource: f, fileOptions });
        });
      }

      resolve(true);
    });
  },
  serverStatusLoad ({ commit }) {
    return UploadsApi.getServerStatus()
      .then( response => {
        if (response.data) {
          commit('SET_SERVER_STATUS', response.data);
        }
      })
      .catch(() => {});
  },
  uploadNextOnAuto ({ getters, state }) {
    return new Promise( reject => {
      if (state.autoUpload) {
        let file = getters.nextFileToUpload;

        if (file) {
          return file.start();
        }
      }

      reject(false);
    });
  },
  uploadStart ({}, file) {
    return new Promise( reject => {
      if (file && file.isUploadable) {
        return file.start();
      }

      reject(false);
    })
  }
};

const getters = {
  ...make.getters(state),
  filesUploaded (state) {
    return state.files.filter( f => f.isUploaded );
  },
  filesUploading (state) {
    return state.files.filter( f => f.isActive );
  },
  filesToUpload (state) {
    return state.files.filter( f => f.isUploadable );
  },
  nextFileToUpload (state, getters) {
    if (getters.filesUploading.length) {
      return null;
    }

    for (let file of state.files) {
      if (file.isUploadable) {
        return file;
      }
    }

    return null;
  },
  maxPostSize (state) {
    return state.serverStatus.maxPostSize;
  },
  uploading (state, getters) {
    return getters.filesUploading.length > 0;
  }
};

const mutations = {
  ...make.mutations(state),
  APPEND_FILE (state, payload) {
    state.files.push(payload);
  },
  APPEND_FILE_CHUNKS (state, { file, chunks }) {
    if (Array.isArray(chunks)) {
      chunks.forEach( chunk => {
        if ((typeof chunk === 'object') && (chunk.constructor.name === 'UploadFileChunk')) {
          file.chunks.push(chunk);
        }
      });
    } else if ((typeof chunks === 'object') && (chunks.constructor.name === 'UploadFileChunk')) {
      file.chunks.push(chunks);
    }
  },
  CLEAR (state) {
    state.files = [];
  },
  DELETE_FILE (state, payload) {
    state.files = state.files.filter( f => f.id !== payload.id );
  },
  DELETE_FILES_BY_ID (state, payload) {
    state.files = state.files.filter( f => payload.indexOf(f.id) > -1 );
  },
  SET_CHUNK_PROPERTY (state, { chunk, name, value }) {
    chunk[name] = value;
  },
  SET_FILE_PROPERTY (state, { file, name, value }) {
    file[name] = value;
  },
  UPDATE_FILE (state, payload) {
    state.files = state.files.map( f => f.index === payload.index ? payload : f );
  }
};

export default {
  namespaced: true,
  state,
  actions,
  getters,
  mutations
}
