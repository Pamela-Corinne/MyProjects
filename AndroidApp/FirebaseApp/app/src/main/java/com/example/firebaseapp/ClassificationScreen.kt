package com.example.firebaseapp

import android.content.Context
import androidx.compose.runtime.*
import androidx.compose.foundation.layout.*
import androidx.compose.material3.Button
import androidx.compose.material3.Text
import androidx.compose.material3.TextField
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp

@Composable
fun ClassificationScreen(context: Context) {
    val classifier = NutritionClassifier(context)

    var age by remember { mutableStateOf("") }
    var weight by remember { mutableStateOf("") }
    var height by remember { mutableStateOf("") }
    var result by remember { mutableStateOf("") }

    Column(modifier = Modifier.padding(16.dp)) {
        TextField(value = age, onValueChange = { age = it })
        TextField(value = weight, onValueChange = { weight = it })
        TextField(value = height, onValueChange = { height = it })

        Button(onClick = {
            result = classifier.classify(age.toFloat(), weight.toFloat(), height.toFloat())
        }) {
            Text("Classify")
        }

        Text("Result: $result")
    }
}
