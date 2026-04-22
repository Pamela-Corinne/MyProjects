package com.example.firebaseapp

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.firebase.auth.FirebaseAuth
import com.google.firebase.firestore.ktx.firestore
import com.google.firebase.ktx.Firebase

class SignUpActivity : AppCompatActivity() {

    private lateinit var auth: FirebaseAuth
    private val db = Firebase.firestore

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_sign_up)

        auth = FirebaseAuth.getInstance()

        val emailEditText = findViewById<EditText>(R.id.emailEditText)
        val passwordEditText = findViewById<EditText>(R.id.passwordEditText)
        val confirmPasswordEditText = findViewById<EditText>(R.id.confirmPasswordEditText)
        val signUpButton = findViewById<Button>(R.id.signUpButton)
        val loginTextView = findViewById<TextView>(R.id.loginTextView)

        signUpButton.setOnClickListener {
            val email = emailEditText.text.toString().trim()
            val password = passwordEditText.text.toString().trim()
            val confirmPassword = confirmPasswordEditText.text.toString().trim()

            if (password == confirmPassword) {
                auth.createUserWithEmailAndPassword(email, password)
                    .addOnCompleteListener(this) { task ->
                        if (task.isSuccessful) {
                            val uid = task.result?.user?.uid ?: return@addOnCompleteListener

                            // 🔐 Store in pendingUsers instead of approvedUsers
                            val user = hashMapOf(
                                "uid" to uid,
                                "email" to email
                            )

                            db.collection("pendingUsers").document(uid)
                                .set(user)
                                .addOnSuccessListener {
                                    Toast.makeText(
                                        baseContext,
                                        "Account created! Wait for admin approval.",
                                        Toast.LENGTH_LONG
                                    ).show()

                                    // Sign out the user immediately to prevent login
                                    auth.signOut()

                                    startActivity(Intent(this, LoginActivity::class.java))
                                    finish()
                                }
                                .addOnFailureListener { exception ->
                                    Toast.makeText(
                                        baseContext,
                                        "Error saving request: ${exception.message}",
                                        Toast.LENGTH_LONG
                                    ).show()
                                }
                        } else {
                            Toast.makeText(
                                baseContext,
                                "Sign-up Failed: ${task.exception?.message}",
                                Toast.LENGTH_LONG
                            ).show()
                        }
                    }
            } else {
                Toast.makeText(this, "Passwords do not match", Toast.LENGTH_SHORT).show()
            }
        }

        loginTextView.setOnClickListener {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
        }
    }
}
