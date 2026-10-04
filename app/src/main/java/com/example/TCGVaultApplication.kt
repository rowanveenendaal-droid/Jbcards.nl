package com.example

import android.app.Application
import android.system.Os

class TCGVaultApplication : Application() {
    companion object {
        init {
            try {
                Os.setenv("LIBGL_ALWAYS_SOFTWARE", "1", true)
                Os.setenv("MESA_LOADER_DRIVER_OVERRIDE", "swrast", true)
                Os.setenv("GALLIUM_DRIVER", "llvmpipe", true)
            } catch (t: Throwable) {
                // Ignore
            }
        }
    }

    override fun onCreate() {
        super.onCreate()
        try {
            Os.setenv("LIBGL_ALWAYS_SOFTWARE", "1", true)
            Os.setenv("MESA_LOADER_DRIVER_OVERRIDE", "swrast", true)
            Os.setenv("GALLIUM_DRIVER", "llvmpipe", true)
        } catch (t: Throwable) {
            // Ignore
        }
    }
}
