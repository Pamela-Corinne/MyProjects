package com.example.firebaseapp

import android.os.Bundle
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import com.google.firebase.firestore.ktx.firestore
import com.google.firebase.ktx.Firebase
import org.tensorflow.lite.Interpreter
import java.io.FileInputStream
import java.nio.ByteBuffer
import java.nio.ByteOrder
import java.nio.MappedByteBuffer
import java.nio.channels.FileChannel
import org.tensorflow.lite.support.tensorbuffer.TensorBuffer
import org.tensorflow.lite.DataType
import androidx.appcompat.app.AlertDialog

class AddChildProfileActivity : AppCompatActivity() {

    private val db = Firebase.firestore
    private lateinit var nameEditText: EditText
    private lateinit var genderRadioGroup: RadioGroup
    private lateinit var ageEditText: EditText
    private lateinit var heightEditText: EditText
    private lateinit var weightEditText: EditText
    private lateinit var uploadButton: Button
    private lateinit var classifyButton: Button
    private lateinit var interpreter: Interpreter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_add_child_profile)

        nameEditText = findViewById(R.id.nameEditText)
        genderRadioGroup = findViewById(R.id.genderRadioGroup)
        ageEditText = findViewById(R.id.ageEditText)
        heightEditText = findViewById(R.id.heightEditText)
        weightEditText = findViewById(R.id.weightEditText)
        uploadButton = findViewById(R.id.uploadButton)
        classifyButton = findViewById(R.id.classifyButton)

        // Load the TensorFlow Lite model
        interpreter = Interpreter(loadModelFile())

        uploadButton.setOnClickListener { uploadData() }
        classifyButton.setOnClickListener { classifyChild() }
        classifyButton.isEnabled = false
    }

    private fun loadModelFile(): MappedByteBuffer {
        val fileDescriptor = assets.openFd("model.tflite")
        val inputStream = FileInputStream(fileDescriptor.fileDescriptor)
        val fileChannel = inputStream.channel
        val startOffset = fileDescriptor.startOffset
        val declaredLength = fileDescriptor.declaredLength
        return fileChannel.map(FileChannel.MapMode.READ_ONLY, startOffset, declaredLength)
    }

    private fun uploadData() {
        val name = nameEditText.text.toString()
        val gender = if (genderRadioGroup.checkedRadioButtonId == R.id.maleRadioButton) "Male" else "Female"
        val age = ageEditText.text.toString().toFloatOrNull() ?: 0.0f
        val height = heightEditText.text.toString().toFloatOrNull() ?: 0.0f
        val weight = weightEditText.text.toString().toFloatOrNull() ?: 0.0f

        if (name.isBlank() || age <= 0 || height <= 0 || weight <= 0) {
            Toast.makeText(this, "Please fill in all fields correctly", Toast.LENGTH_SHORT).show()
            return
        }

        // Perform classification before uploading data
        val classificationText = classifyChild()

        val childProfile = hashMapOf(
            "name" to name,
            "gender" to gender,
            "age" to age,
            "height" to height,
            "weight" to weight,
            "classification" to classificationText // Add classification to the childProfile map
        )

        db.collection("childProfile")
            .add(childProfile)
            .addOnSuccessListener { documentReference ->
                Toast.makeText(this, "Child profile added with ID: ${documentReference.id}", Toast.LENGTH_SHORT).show()
                classifyButton.isEnabled = true
            }
            .addOnFailureListener { e ->
                Toast.makeText(this, "Error adding child profile: ${e.message}", Toast.LENGTH_SHORT).show()
            }
    }

    private fun classifyChild(): String {
        val gender = if (genderRadioGroup.checkedRadioButtonId == R.id.maleRadioButton) 0f else 1f
        val age = ageEditText.text.toString().toFloatOrNull() ?: 0.0f
        val height = heightEditText.text.toString().toFloatOrNull() ?: 0.0f
        val weight = weightEditText.text.toString().toFloatOrNull() ?: 0.0f

        // Create input tensor (1D array)
        val inputTensor = TensorBuffer.createFixedSize(intArrayOf(4), DataType.FLOAT32)
        inputTensor.loadArray(floatArrayOf(gender, age, height, weight))

        // Run inference
        val outputTensor = interpreter.getOutputTensor(0)
        val outputBuffer = TensorBuffer.createFixedSize(outputTensor.shape(), DataType.FLOAT32)
        interpreter.run(inputTensor.buffer, outputBuffer.buffer)

        // Get the predicted class (using argmax for the highest probability)
        val probabilities = outputBuffer.floatArray
        val predictedClassIndex = probabilities.indexOfFirst { !it.isNaN() && it == probabilities.filter { !it.isNaN() }.maxOrNull() }

        val classificationText = when (predictedClassIndex) {
            0 -> "Normal"
            1 -> "Obese"
            2 -> "Overweight"
            3 -> "Severely Underweight"
            4 -> "Underweight"
            else -> "Unknown" // Handle cases where prediction fails
        }

        // Recommendations based on classification
        val recommendation = when (classificationText) {
            "Underweight" -> "Increase calorie intake, eat nutrient-rich foods, and consult a doctor or nutritionist."
            "Normal" -> "Maintain a balanced diet and regular exercise."
            "Overweight" -> "Reduce calorie intake, increase physical activity, and consult a doctor or nutritionist."
            "Obese" -> "Significantly reduce calorie intake, increase physical activity, and consult a doctor or nutritionist for a weight loss plan."
            "Severely Underweight" -> "Seek immediate medical attention; this requires specialized care."
            else -> "Consult a doctor or nutritionist for personalized advice."
        }

        // Build the message string with classification and recommendation
        val message = buildString {
            append("Classification: $classificationText\n\n")
            append("Recommendation:\n$recommendation")
        }

        // Display classification and recommendation in a dialog
        AlertDialog.Builder(this)
            .setTitle("Nutrition Classification")
            .setMessage(message)
            .setPositiveButton("OK") { dialog, _ -> dialog.dismiss() }
            .show()

        return classificationText
    }
}