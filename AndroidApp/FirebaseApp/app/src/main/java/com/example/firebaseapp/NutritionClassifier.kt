package com.example.firebaseapp

import android.content.Context
import org.tensorflow.lite.Interpreter
import java.io.FileInputStream
import java.nio.MappedByteBuffer
import java.nio.channels.FileChannel

class NutritionClassifier(context: Context) {
    private var interpreter: Interpreter

    init {
        val modelFile = loadModelFile(context)
        interpreter = Interpreter(modelFile)
    }

    private fun loadModelFile(context: Context): MappedByteBuffer {
        val fileDescriptor = context.assets.openFd("model.tflite")
        val inputStream = FileInputStream(fileDescriptor.fileDescriptor)
        val fileChannel = inputStream.channel
        return fileChannel.map(FileChannel.MapMode.READ_ONLY, fileDescriptor.startOffset, fileDescriptor.declaredLength)
    }

    fun classify(age: Float, weight: Float, height: Float): String {
        val input = floatArrayOf(age, weight, height)
        val output = Array(1) { FloatArray(1) }
        interpreter.run(input, output)

        return when (output[0][0].toInt()) {
            0 -> "Severely Underweight"
            1 -> "Underweight"
            2 -> "Normal"
            3 -> "Overweight"
            4 -> "Obese"
            else -> "Unknown"
        }
    }
}
