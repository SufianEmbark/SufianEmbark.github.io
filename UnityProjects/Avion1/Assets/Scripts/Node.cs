using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Node : MonoBehaviour
{
    // Start is called before the first frame update
    void Start()
    {
        this.enabled = true;
    }

    // Update is called once per frame
    void Update()
    {
        
    }

    void OnTriggerExit(Collider other)
    {
        StartCoroutine(WaitForCar());
        this.enabled = false;
    }

    IEnumerator WaitForCar()
    {


        yield return new WaitForSeconds(2);

        this.enabled = true;
    }
}
