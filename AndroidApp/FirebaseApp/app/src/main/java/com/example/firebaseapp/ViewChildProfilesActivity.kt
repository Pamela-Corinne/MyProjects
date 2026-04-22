package com.example.firebaseapp

import android.os.Bundle
import android.util.Log
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.firebaseapp.databinding.ActivityViewChildProfilesBinding
import com.google.firebase.firestore.ktx.firestore
import com.google.firebase.ktx.Firebase

class ViewChildProfilesActivity : AppCompatActivity() {
    private lateinit var binding: ActivityViewChildProfilesBinding
    private val db = Firebase.firestore
    private lateinit var childProfilesAdapter: ChildProfilesAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityViewChildProfilesBinding.inflate(layoutInflater)
        setContentView(binding.root)

        binding.childProfilesRecyclerView.layoutManager = LinearLayoutManager(this)
        childProfilesAdapter = ChildProfilesAdapter(this)
        binding.childProfilesRecyclerView.adapter = childProfilesAdapter

        loadChildProfiles()

        binding.backButton.setOnClickListener {
            finish()
        }
    }

    private fun loadChildProfiles() {
        db.collection("childProfile").get()
            .addOnSuccessListener { querySnapshot ->
                val childProfiles = mutableListOf<ChildProfile>()

                for (document in querySnapshot) {
                    // Log full field info for debugging
                    Log.d("ChildDataDebug", "Raw: name=${document.get("name")} (${document.get("name")?.javaClass}), " +
                            "age=${document.get("age")} (${document.get("age")?.javaClass}), " +
                            "height=${document.get("height")}, weight=${document.get("weight")}, " +
                            "gender=${document.get("gender")}, classification=${document.get("classification")}")

                    // Safely fetch and convert fields
                    val name = document.getString("name") ?: "Unknown"
                    val age = document.getDouble("age")?.toString() ?: "N/A"
                    val height = document.getDouble("height")?.toString() ?: "N/A"
                    val weight = document.getDouble("weight")?.toString() ?: "N/A"
                    val gender = document.getString("gender") ?: "N/A"
                    val classification = document.getString("classification") ?: "Unclassified"

                    childProfiles.add(
                        ChildProfile(
                            name = name,
                            age = age,
                            height = height,
                            weight = weight,
                            gender = gender,
                            classification = classification
                        )
                    )
                }

                childProfilesAdapter.updateChildProfiles(childProfiles)

                if (childProfiles.isEmpty()) {
                    showErrorToast("No child profiles found.")
                }
            }
            .addOnFailureListener { exception ->
                showErrorToast("Error loading child profiles: ${exception.message}")
                Log.e("ViewChildProfilesActivity", "Error loading child profiles", exception)
            }
    }

    private fun showErrorToast(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
    }
}
