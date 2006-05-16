/*
 * Alias .obj loader for processing
 * programmed by Tatsuya SAITO / UCLA Design | Media Arts 
 * Created on 2005/04/17
 *
 * 
 *  
 */


package objLoader;

import processing.core.PImage;

/**
 * @author tatsuyas
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */
public class Material {
	public PImage map_Ka;
	public PImage map_Kd;
	public float[] Ka;
	public float d;
	public Material() {
		Ka = new float[3];
		Ka[0] = 1.0f;
		Ka[1] = 1.0f;
		Ka[2] = 1.0f;
		d = 1.0f;
	}
}
